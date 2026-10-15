import { pushToggle, registerServiceWorker } from './push';
import { registerUi } from './ui';

registerServiceWorker();

document.addEventListener('alpine:init', () => {
    registerUi(window.Alpine);
    window.Alpine.data('pushToggle', pushToggle);
});
