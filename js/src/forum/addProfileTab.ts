import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addProfileTab() {
  extend('flarum/forum/components/UserPage', 'navItems', function (items) {
    const user = (this as any).user;
    if (!user) return;

    const canView = app.forum.attribute('canViewRewind');
    const canModerate = app.forum.attribute('canModerateRewind');
    const enabled = app.forum.attribute('rewindEnabled');

    if ((enabled && canView) || canModerate) {
      items.add(
        'rewind',
        LinkButton.component(
          {
            href: app.route('huseyinfiliz-rewind.profile', { username: user.slug() }),
            icon: 'fas fa-history',
          },
          app.translator.trans('huseyinfiliz-rewind.forum.profile.tab')
        ),
        80
      );
    }
  });
}
