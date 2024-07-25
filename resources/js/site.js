import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import intersect from '@alpinejs/intersect';
import Precognition from 'laravel-precognition-alpine';
import { swiffyslider } from 'swiffy-slider';

import "swiffy-slider/css";

window.Alpine = Alpine;
window.swiffyslider = swiffyslider;

Alpine.plugin(Precognition);
Alpine.plugin(intersect);

if (window.livewireScriptConfig?.csrf === 'STATAMIC_CSRF_TOKEN') {
    document.addEventListener('statamic:nocache.replaced', () => Livewire.start());
} else {
    Livewire.start();
}

window.addEventListener('load', () => {
    window.swiffyslider.init();
});
