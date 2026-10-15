import { pushToggle, registerServiceWorker } from './push';

registerServiceWorker();

document.addEventListener('alpine:init', () => {
    window.Alpine.data('pushToggle', pushToggle);
});
