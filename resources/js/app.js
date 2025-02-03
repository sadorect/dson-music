import './bootstrap';
import './tracks.js';
import Alpine from 'alpinejs';
import './search';
import playerControls from './components/player';
import { submitComment } from './comments';

window.submitComment = submitComment;

// Register playerControls as an Alpine data component
document.addEventListener('alpine:init', () => {
    Alpine.data('playerControls', playerControls)
});

window.Alpine = Alpine;
Alpine.start();
