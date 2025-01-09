import './bootstrap';
import './tracks.js';
import Alpine from 'alpinejs';
import './search';
import playerControls from './components/player';

window.playerControls = playerControls;
window.Alpine = Alpine;

Alpine.start();


