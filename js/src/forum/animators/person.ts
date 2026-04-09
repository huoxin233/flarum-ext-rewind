import { animate, stagger } from 'animejs';

export function animatePerson(root: Element, tl: any) {
  const avatar = root.querySelector('.rw-person-avatar');
  const duo = root.querySelector('.rw-person-duo');
  const ownerAvatar = root.querySelector('.rw-person-duo-avatar--owner');
  const friendAvatar = root.querySelector('.rw-person-duo-avatar--friend');
  const chars = root.querySelectorAll('.rw-person-char');
  const stats = root.querySelectorAll('.rw-person-stat');

  if (duo) {
    // Sequential spin: owner first, then friend
    if (ownerAvatar) {
      animate(ownerAvatar, {
        opacity: [0, 1],
        scale: [0, 1],
        rotateY: [360, 0],
        duration: 1000,
        ease: 'outExpo',
      });
    }
    if (friendAvatar) {
      setTimeout(() => {
        animate(friendAvatar, {
          opacity: [0, 1],
          scale: [0, 1],
          rotateY: [-360, 0],
          duration: 1000,
          ease: 'outExpo',
        });
      }, 400);
    }

    // Gentle bob
    setTimeout(() => {
      animate(duo, {
        translateY: [0, -6, 0],
        duration: 3000,
        loop: true,
        ease: 'easeInOutSine',
      });
    }, 1500);
  } else if (avatar) {
    tl.add(avatar, { opacity: [0, 1], scale: [0, 1], rotateY: [-180, 0], duration: 1200, ease: 'outElastic(1, .5)' }, 0);

    setTimeout(() => {
      animate(avatar, {
        translateY: [0, -8, 0],
        duration: 3000,
        loop: true,
        ease: 'easeInOutSine',
      });
    }, 1400);
  }

  if (chars.length) {
    tl.add(
      chars,
      {
        opacity: [0, 1],
        translateY: [50, 0],
        translateZ: [100, 0],
        rotateX: [-90, 0],
        delay: stagger(40, { start: 800 }),
        duration: 800,
        ease: 'outBack',
      },
      0
    );
  }

  // Interaction stats stagger in
  if (stats.length) {
    animate(stats, {
      opacity: [0, 1],
      translateY: [15, 0],
      scale: [0.9, 1],
      delay: stagger(80, { start: 1400 }),
      duration: 500,
      ease: 'outExpo',
    });
  }

  // Mention cards stagger in
  const mentionCards = root.querySelectorAll('.rw-mention-card');
  if (mentionCards.length) {
    animate(mentionCards, {
      opacity: [0, 1],
      translateX: [-30, 0],
      delay: stagger(200, { start: 200 }),
      duration: 600,
      ease: 'outExpo',
    });
  }
}
