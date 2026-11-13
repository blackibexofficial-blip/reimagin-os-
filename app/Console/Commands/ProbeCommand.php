<?php

namespace App\Console\Commands;

use App\Support\Blocks\BlockRegistry;
use Illuminate\Console\Command;

class ProbeCommand extends Command
{
    protected $signature = 'app:probe {--render : Also render one document to exercise the view layer}';

    protected $description = 'Development probe: reports wiring that is hard to see through route:list.';

    public function handle(): int
    {
        $this->info('blocks: '.count(BlockRegistry::all()));
        $this->info('groups: '.implode(', ', array_keys(BlockRegistry::library())));
        $this->info('users: '.\App\Models\User::query()->count());
        $this->info('pages: '.\App\Models\Page::query()->count());

        if ($this->option('render')) {
            $html = view('probe', ['blocks' => BlockRegistry::normalize([
                ['type' => 'heading', 'attrs' => ['text' => 'Rendered from the probe']],
            ])])->render();
            $this->info('rendered: '.strlen($html).' bytes');
        }

        return self::SUCCESS;
    }
}
