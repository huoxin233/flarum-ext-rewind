import { animate, stagger } from 'animejs';

export function animateBignum(root: Element, tl: any, key: string) {
  const num = root.querySelector('.rw-bignum');
  if (!num) return;

  // Animate the wrap container if present
  const wrap = root.querySelector('.rw-bignum-wrap');
  if (wrap) {
    tl.add(wrap, { opacity: [0, 1], scale: [0.9, 1], duration: 800, ease: 'outExpo' }, 0);
  }

  const targetAttr = num.getAttribute('data-target');
  if (targetAttr) {
    const target = parseInt(targetAttr);
    if (target <= 0) return;

    // Different entrance animation per metric — dramatic 3D entrances
    if (key === 'post_count') {
      // Slam down from above with perspective
      tl.add(num, { opacity: [0, 1], translateY: [-200, 0], rotateX: [-20, 0], scale: [1.5, 1], duration: 1200, ease: 'outBounce' }, 0);
    } else if (key === 'discussion_count') {
      // Explode from center with 3D rotation
      tl.add(num, { opacity: [0, 1], scale: [0, 1], rotateY: [180, 0], duration: 1400, ease: 'outElastic(1, .4)' }, 0);
    } else if (key === 'active_days') {
      // Full 3D flip + rise
      tl.add(num, { opacity: [0, 1], rotateX: [180, 0], rotateZ: [15, 0], scale: [2.5, 1], duration: 1200, ease: 'outExpo' }, 0);
    } else if (key === 'word_count') {
      // Typewriter sweep from left with depth
      tl.add(num, { opacity: [0, 1], translateX: [-300, 0], rotateY: [45, 0], scale: [0.8, 1], duration: 1200, ease: 'outExpo' }, 0);
    } else if (key === 'badges_earned') {
      // Medal-drop with spin
      tl.add(num, { opacity: [0, 1], scale: [4, 1], rotateZ: [-360, 0], duration: 1400, ease: 'outElastic(1, .5)' }, 0);
    } else if (key === 'best_answers') {
      // Rise with 3D tilt
      tl.add(num, { opacity: [0, 1], translateY: [120, 0], rotateX: [-30, 0], scale: [0.4, 1], duration: 1200, ease: 'outBack' }, 0);
    } else {
      // Default: 3D scale + fade
      tl.add(num, { opacity: [0, 1], scale: [3, 1], rotateY: [-90, 0], duration: 1100, ease: 'outExpo' }, 0);
    }

    // Count-up animation (shared across all)
    const obj = { val: 0 };
    animate(obj, {
      val: target,
      duration: 1800,
      ease: 'outExpo',
      onUpdate: () => {
        num.textContent = Math.round(obj.val).toLocaleString();
      },
    });

    // Gentle breathing pulse with subtle 3D tilt
    setTimeout(() => {
      animate(num, {
        scale: [1, 1.03, 1],
        rotateY: [0, 2, 0],
        duration: 4000,
        loop: true,
        ease: 'easeInOutSine',
      });
    }, 2000);

    // Badge items entrance (if present)
    const badgeItems = root.querySelectorAll('.rw-badge-item');
    if (badgeItems.length > 0) {
      animate(badgeItems, {
        opacity: [0, 1],
        translateY: [20, 0],
        scale: [0.8, 1],
        delay: stagger(100, { start: 700 }),
        duration: 600,
        ease: 'outBack',
      });
    }
  } else {
    // Bignum text layout (non-numeric)
    (num as HTMLElement).style.opacity = '1';
    const chars = root.querySelectorAll('.rw-bignum-char');
    if (chars.length) {
      tl.add(
        chars,
        {
          opacity: [0, 1],
          scale: [3, 1],
          rotateY: [-90, 0],
          translateZ: [400, 0],
          delay: stagger(100),
          duration: 800,
          ease: 'easeOutElastic(1, .5)',
        },
        0
      );
    }
  }
}
