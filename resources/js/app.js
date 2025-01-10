import './bootstrap';
import './tracks.js';
import Alpine from 'alpinejs';
import './search';
import playerControls from './components/player';
import './comments';

window.playerControls = playerControls;
window.Alpine = Alpine;

Alpine.start();


