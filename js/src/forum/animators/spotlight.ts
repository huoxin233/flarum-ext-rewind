import { animate, stagger, random } from 'animejs';

export function animateSpotlight(root: Element, tl: any) {
  const chars = root.querySelectorAll('.rw-spotlight-char');
  const spotlightWrap = root.querySelector('.rw-spotlight-value');
  if (spotlightWrap) (spotlightWrap as HTMLElement).style.opacity = '1';
  if (chars.length) {
    tl.add(
      chars,
      {
        opacity: [0, 1],
        scale: [0.1, 1],
        translateY: () => [random(-100, 100), 0],
        translateX: () => [random(-100, 100), 0],
        rotateZ: () => [random(-90, 90), 0],
        delay: stagger(50, { from: 'center' }),
        duration: 1200,
        ease: 'outElastic(1, .6)',
      },
      0
    );

    // Continuous gentle float — full round-trip keyframes
    setTimeout(() => {
      chars.forEach((el) => {
        const dy = random(-4, 4);
        animate(el, {
          translateY: [0, dy, 0],
          duration: random(2500, 3500),
          loop: true,
          ease: 'easeInOutSine',
        });
      });
    }, 1500);
  }
}
