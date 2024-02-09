import Alpine from 'alpinejs'
import Precognition from 'laravel-precognition-alpine';
import Swiper from 'swiper';
import 'swiper/css/swiper.css';

window.Alpine = Alpine;
window.Swiper = Swiper;

Alpine.plugin(Precognition);
Alpine.start();
