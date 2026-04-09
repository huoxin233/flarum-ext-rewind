import { animate, stagger } from 'animejs';

export function animateChart(root: Element, tl: any) {
  const month = root.querySelector('.rw-chart-peak');
  if (month) tl.add(month, { opacity: [0, 1], scale: [0.2, 1], translateZ: [300, 0], duration: 800, ease: 'easeOutElastic(1, .5)' }, 0);

  const bars = root.querySelectorAll('.rw-bar-fill');
  if (bars.length > 0) {
    animate(bars, {
      scaleY: [0, 1.5, 1],
      translateZ: [0, 50, 0],
      delay: stagger(80, { start: 400 }),
      duration: 1000,
      ease: 'easeOutElastic(1, .5)',
    });
  }

  // Labels fade in after bars
  const labels = root.querySelectorAll('.rw-bar-label');
  if (labels.length > 0) {
    animate(labels, {
      opacity: [0, 0.4],
      delay: stagger(60, { start: 800 }),
      duration: 400,
      ease: 'outExpo',
    });
  }
}
