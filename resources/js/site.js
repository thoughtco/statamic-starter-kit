import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import intersect from '@alpinejs/intersect';
import Precognition from 'laravel-precognition-alpine';
import Swiper from 'swiper/bundle';

import FormHandler from './components/forms';
import MapHandler from './components/maps';

window.Alpine = Alpine;
window.Swiper = Swiper;

Alpine.plugin(Precognition);
Alpine.plugin(intersect);

Alpine.data('formHandler', FormHandler);
Alpine.data('googleMap', MapHandler);

if (window.livewireScriptConfig?.csrf === 'STATAMIC_CSRF_TOKEN') {
    document.addEventListener('statamic:nocache.replaced', () => Livewire.start());
} else {
    Livewire.start();
}
