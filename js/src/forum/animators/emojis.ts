import { animate, stagger, random } from 'animejs';

export function animateEmojis(root: Element, tl: any) {
  const emojis = root.querySelectorAll('.rw-emoji-item');
  const counts = root.querySelectorAll('.rw-emoji-count');

  if (emojis.length > 0) {
    tl.add(
      emojis,
      {
        opacity: [0, 1],
        scale: [0, 2, 1],
        rotateZ: () => [random(-360, 360), 0],
        translateY: () => [random(-200, 200), 0],
        translateX: () => [random(-200, 200), 0],
        delay: stagger(150, { start: 100 }),
        duration: 1200,
        ease: 'easeOutElastic(1, .5)',
      },
      0
    );

    // Continuous gentle float — full round-trip keyframes
    setTimeout(() => {
      emojis.forEach((el) => {
        const dy = random(-7, -3);
        animate(el, {
          translateY: [0, dy, 0],
          duration: random(2400, 3600),
          loop: true,
          ease: 'easeInOutSine',
        });
      });
    }, 2000);
  }

  if (counts.length > 0) {
    tl.add(
      counts,
      {
        opacity: [0, 1],
        translateY: [10, 0],
        delay: stagger(100),
        duration: 600,
        ease: 'outExpo',
      },
      1600
    );
  }
}
