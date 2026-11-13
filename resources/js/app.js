import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';
import { pageBuilder } from './builder';

Alpine.plugin(sort);
window.Alpine = Alpine;

Alpine.data('pageBuilder', pageBuilder);

Alpine.start();
