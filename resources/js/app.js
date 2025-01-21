import './bootstrap';
import './tracks.js';
import Alpine from 'alpinejs';
import './search';
import playerControls from './components/player';
import './comments';

window.submitComment = submitComment;

window.playerControls = playerControls;
window.Alpine = Alpine;

Alpine.start();


