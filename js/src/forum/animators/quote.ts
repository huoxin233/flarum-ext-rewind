import { animate, stagger } from 'animejs';

export function animateQuote(root: Element, tl: any) {
  // Post preview card (new style with excerpt)
  const preview = root.querySelector('.rw-post-preview');
  if (preview) {
    tl.add(preview, { opacity: [0, 1], translateY: [30, 0], scale: [0.95, 1], duration: 800, ease: 'outExpo' }, 0);
    const meta = preview.querySelector('.rw-quote-meta');
    if (meta) tl.add(meta, { opacity: [0, 0.6], duration: 400, ease: 'outExpo' }, 600);
    return;
  }

  // Fallback: old quote style (no excerpt)
  const mark = root.querySelector('.rw-quote-mark');
  if (mark) tl.add(mark, { opacity: [0, 0.25], scale: [4, 1], rotateZ: [-180, 0], duration: 800, ease: 'outExpo' }, 0);

  const chars = root.querySelectorAll('.rw-quote-char');
  const quoteWrap = root.querySelector('.rw-quote-text');
  if (quoteWrap) (quoteWrap as HTMLElement).style.opacity = '1';
  if (chars.length) {
    tl.add(
      chars,
      {
        opacity: [0, 1],
        rotateX: [-90, 0],
        translateZ: [200, 0],
        translateY: [40, 0],
        delay: stagger(20),
        duration: 600,
        ease: 'outBack',
      },
      300
    );
  }

  const meta = root.querySelector('.rw-quote-meta');
  if (meta) tl.add(meta, { opacity: [0, 1], translateY: [20, 0], duration: 600, ease: 'outExpo' }, 800);

  setTimeout(() => {
    if (mark) {
      animate(mark, {
        scale: [1, 1.1, 1],
        opacity: [0.25, 0.15, 0.25],
        duration: 4000,
        loop: true,
        ease: 'easeInOutSine',
      });
    }
  }, 1200);
}
