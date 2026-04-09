import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addUserMenuLink() {
  extend('flarum/forum/components/SessionDropdown', 'items', function (items) {
    if (!app.forum.attribute('rewindShowMenu')) return;
    if (!app.forum.attribute('rewindEnabled') && !app.forum.attribute('canModerateRewind')) return;

    const user = app.session.user;
    if (!user) return;

    const year = app.forum.attribute<number>('rewindActiveYear');

    items.add(
      'rewind',
      LinkButton.component(
        {
          href: app.route('huseyinfiliz-rewind.profile', { username: user.slug() }),
          icon: 'fas fa-history',
        },
        app.translator.trans('huseyinfiliz-rewind.forum.user_menu.rewind_link', { year })
      ),
      -90
    );
  });
}
