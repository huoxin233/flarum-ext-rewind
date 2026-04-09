import { animate, stagger } from 'animejs';

export function animateClock(root: Element, tl: any) {
  const ring = root.querySelector('.rw-clock-ring');
  const peakLabel = root.querySelector('.rw-clock-peak-label');
  const typeLabel = root.querySelector('.rw-clock-type-label');
  const stars = root.querySelectorAll('.rw-clock-star');
  const bars = root.querySelectorAll('.rw-clock-bar');
  const hands = root.querySelector('.rw-clock-hands');

  // Phase 1: Ring scales up from tiny while hands spin clockwise
  if (ring) {
    tl.add(
      ring,
      {
        opacity: [0, 1],
        scale: [0.05, 1],
        duration: 1600,
        ease: 'outExpo',
      },
      0
    );
  }

  // Hands spin 720° clockwise (2 full rotations) while ring grows, then settle
  if (hands) {
    animate(hands as unknown as HTMLElement, {
      rotate: [720, 0],
      duration: 2000,
      ease: 'outExpo',
    });
  }

  // Phase 2: Bars sweep in clockwise from hour 0
  if (bars.length > 0) {
    animate(bars, {
      opacity: [0, 1],
      delay: stagger(45, { start: 1000 }),
      duration: 400,
      ease: 'outExpo',
    });
  }

  // Stars twinkling in (for night owl)
  if (stars.length > 0) {
    animate(stars, {
      opacity: [0, 1],
      scale: [0, 1.5, 1],
      delay: stagger(80, { start: 2400 }),
      duration: 1000,
      ease: 'outElastic(1, .5)',
    });

    setTimeout(() => {
      animate(stars, {
        opacity: [1, 0.3, 1],
        scale: [1, 0.8, 1],
        delay: stagger(200),
        duration: 2000,
        loop: true,
        ease: 'easeInOutSine',
      });
    }, 3800);
  }

  // Peak hour label
  if (peakLabel) {
    tl.add(
      peakLabel,
      {
        opacity: [0, 1],
        scale: [2, 1],
        duration: 800,
        ease: 'outExpo',
      },
      2400
    );
  }

  // Type label
  if (typeLabel) {
    tl.add(
      typeLabel,
      {
        opacity: [0, 1],
        translateY: [20, 0],
        duration: 600,
        ease: 'outExpo',
      },
      2800
    );
  }
}
