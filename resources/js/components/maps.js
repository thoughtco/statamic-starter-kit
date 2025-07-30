import { MarkerClusterer } from "@googlemaps/markerclusterer";

const MapHandler = (settings, locations) => ({
    consentGiven: false,
    locations: locations,
    map: false,
    settings: settings,

    updateConsentGiven() {
        this.consentGiven = ConsentPanel.hasConsentedTo('third-party');
        this.$nextTick(() => this.initMap());
    },

    init() {
        let key = this.settings.apiKey;
        delete this.settings.apiKey;

        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
            key: key,
            v: "weekly",
        });

        this.updateConsentGiven();

        this.$watch('locations', () => this.initMap());

        window.addEventListener('statamic-consentpanel:consent-changed', () => {
            this.updateConsentGiven();
        });
    },

    async initMap() {
        const el = this.$el.querySelector('.map-display');

        if (! el) {
            return;
        }

        el.style.pointerEvents = 'all';

        const { LatLng, LatLngBounds } = await google.maps.importLibrary("core");
        const { Map, InfoWindow } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        const map = new Map(el, Object.assign({}, {
            zoom: 5,
            center: { lat: 0, lng: 0 },
            mapTypeControl: false,
            panControl: true,
            streetViewControl: false,
            zoomControl: true,
            draggable: true,
            shouldCluster: true,

        }, this.settings));

        const bounds = new LatLngBounds();

        const markers = Object.values(this.locations).map((loc, i) => {
            const div = document.createElement('div');
            div.style.transform = 'scale(' + loc.scale ?? 1 + ')';

            const img = document.createElement('img');
            img.src = loc.image;

            div.appendChild(img);

            if (loc.markerHTML) {
                div.insertAdjacentHTML('beforeend', loc.markerHTML);
            }

            const marker = new AdvancedMarkerElement({
                map,
                position: { lat: loc.lat, lng: loc.lng },
                content: div,
            });

            marker.addListener('click', ({ domEvent, latLng }) => {
                const {target} = domEvent;
                map.panTo(latLng);

                console.log(loc)

                if (loc.infoWindow) {
                    infoWindow.open({ anchor: marker, map });
                }

                if (loc.onClick) {
                    loc.onClick(marker, map);
                }
            });

            if (loc.infoWindow) {
                const infoWindow = new InfoWindow({
                    content: '',
                    disableAutoPan: false,
                });
            }

            bounds.extend(new LatLng(loc.lat, loc.lng));

            return marker;
        });

        // bounds can force a zoom change
        // so if we want a fixed zoom then we wait for bounds to change
        if (settings.zoom) {
            google.maps.event.addListenerOnce(map, "bounds_changed", () => map.setZoom(settings.zoom));
        }

        map.fitBounds(bounds);

        if (! settings.shouldCluster) {
            return;
        }

        const renderer = {
            render({ count, position }, stats, map) {

                const svg = `<svg fill="var(--primary-color)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" width="50" height="50">
                    <circle cx="120" cy="120" opacity="1" r="70" />
                    <text x="50%" y="50%" style="fill:#fff" text-anchor="middle" font-size="50" dominant-baseline="middle">${count}</text>
                    </svg>`;
                const title = `Cluster of ${count} markers`,

                    // adjust zIndex to be above other markers
                    zIndex = Number(google.maps.Marker.MAX_ZINDEX) + count;

                // create cluster SVG element
                const parser = new DOMParser();
                const svgEl = parser.parseFromString(svg, "image/svg+xml").documentElement;
                svgEl.setAttribute("transform", "translate(0 25)");

                return new AdvancedMarkerElement({
                    map,
                    position,
                    zIndex,
                    title,
                    content: svgEl,
                });
            }
        };

        new MarkerClusterer({ renderer, markers, map });
    },

        updateLocations(locations) {
            this.locations = locations;
        },

    });

    export default MapHandler;
