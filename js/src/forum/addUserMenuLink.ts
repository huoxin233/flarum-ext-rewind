import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';
import SessionDropdown from 'flarum/forum/components/SessionDropdown';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export default function addUserMenuLink() {
  extend(SessionDropdown.prototype, 'items', function (items: ItemList<Mithril.Children>) {
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
