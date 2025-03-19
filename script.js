const rainContainer = document.querySelector('.rain');
const rainCount = 100;

for (let i = 0; i < rainCount; i++) {
    const raindrop = document.createElement('div');
    raindrop.classList.add('raindrop');
    raindrop.style.left = Math.random() * 100 + 'vw';
    raindrop.style.animationDelay = Math.random() * 2 + 's';
    rainContainer.appendChild(raindrop);
}
