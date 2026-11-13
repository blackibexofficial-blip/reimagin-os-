<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Support\Slug;
use Illuminate\Support\Facades\DB;

/**
 * Page lifecycle: draft edits, publishing, revisions and duplication.
 *
 * Draft and published content live in separate columns so a visible edit in the
 * builder never reaches the public site until publish() is called.
 */
class PageService
{
    public function create(array $data, ?User $user = null): Page
    {
        return DB::transaction(function () use ($data, $user) {
            $page = Page::create([
                'title' => $data['title'],
                'slug' => Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'pages'),
                'status' => 'draft',
                'excerpt' => $data['excerpt'] ?? null,
                'template' => $data['template'] ?? 'default',
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'is_homepage' => (bool) ($data['is_homepage'] ?? false),
                'show_in_navigation' => (bool) ($data['show_in_navigation'] ?? false),
                'navigation_order' => (int) ($data['navigation_order'] ?? 0),
                'author_id' => $user?->id,
                'content' => $data['content'] ?? [],
            ]);

            if ($page->is_homepage) {
                $this->claimHomepage($page);
            }

            return $page;
        });
    }

    public function updateDraft(Page $page, array $data, ?User $user = null): Page
    {
        /* Blocks are written straight to the draft column: the builder owns the
         * content structure, everything else here is page metadata. */
        if (array_key_exists('content', $data)) {
            $page->content = $data['content'];
        }

        $page->fill([
            'title' => $data['title'] ?? $page->title,
            'excerpt' => $data['excerpt'] ?? $page->excerpt,
            'template' => $data['template'] ?? $page->template,
            'seo_title' => $data['seo_title'] ?? $page->seo_title,
            'seo_description' => $data['seo_description'] ?? $page->seo_description,
            'show_in_navigation' => (bool) ($data['show_in_navigation'] ?? false),
            'navigation_order' => (int) ($data['navigation_order'] ?? $page->navigation_order),
        ]);

        if (! empty($data['slug'])) {
            $page->slug = Slug::unique($data['slug'], 'pages', $page->id);
        }

        if (! empty($data['is_homepage'])) {
            $page->is_homepage = true;
            $this->claimHomepage($page);
        }

        $page->save();

        return $page;
    }

    /**
     * Copy the draft into the published snapshot. The public site reads only
     * published_content, so this is the single point where content goes live.
     */
    public function publish(Page $page, ?User $user = null): Page
    {
        return DB::transaction(function () use ($page, $user) {
            $this->writeRevision($page, $user, 'publish', 'Published to the live site');

            $page->forceFill([
                'published_content' => $page->content,
                'status' => 'published',
                'published_at' => now(),
                'scheduled_for' => null,
            ])->save();

            return $page;
        });
    }

    public function unpublish(Page $page, ?User $user = null): Page
    {
        $page->forceFill(['status' => 'draft'])->save();

        return $page;
    }

    public function schedule(Page $page, \DateTimeInterface $when, ?User $user = null): Page
    {
        $page->forceFill([
            'scheduled_for' => $when,
            'status' => 'scheduled',
        ])->save();

        return $page;
    }

    /** Publish every page whose scheduled time has arrived. Safe to run often. */
    public function publishDue(): int
    {
        $due = Page::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($due as $page) {
            $this->publish($page, null);
        }

        return $due->count();
    }

    public function duplicate(Page $page, ?User $user = null): Page
    {
        return DB::transaction(function () use ($page, $user) {
            $copy = $page->replicate(['published_content', 'published_at', 'scheduled_for']);
            $copy->title = $page->title.' (copy)';
            $copy->slug = Slug::unique($page->slug.'-copy', 'pages');
            $copy->status = 'draft';
            $copy->is_homepage = false;
            $copy->published_content = null;
            $copy->published_at = null;
            $copy->author_id = $user?->id ?? $page->author_id;
            $copy->save();

            return $copy;
        });
    }

    public function delete(Page $page): void
    {
        DB::transaction(function () use ($page) {
            $page->revisions()->delete();
            $page->delete();
        });
    }

    public function writeRevision(Page $page, ?User $user, string $label = 'Manual save', ?string $reason = null): PageRevision
    {
        $next = (int) $page->revisions()->max('revision') + 1;

        return PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $user?->id,
            'revision' => $next,
            'content' => $page->content,
            'title' => $page->title,
            'slug' => $page->slug,
            'label' => $label,
            'reason' => $reason,
        ]);
    }

    /** Restore a revision into the draft; publishing stays a separate action. */
    public function restoreRevision(Page $page, PageRevision $revision, ?User $user = null): Page
    {
        return DB::transaction(function () use ($page, $revision, $user) {
            $page->forceFill([
                'content' => $revision->content,
                'title' => $revision->title,
            ])->save();

            $this->writeRevision($page, $user, 'restore', 'Restored revision '.$revision->revision);

            return $page;
        });
    }

    private function claimHomepage(Page $page): void
    {
        Page::query()
            ->where('id', '!=', $page->id)
            ->where('is_homepage', true)
            ->update(['is_homepage' => false]);
    }
}
