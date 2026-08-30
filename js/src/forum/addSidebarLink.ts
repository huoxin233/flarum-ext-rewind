import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';
import IndexPage from 'flarum/forum/components/IndexPage';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export default function addSidebarLink() {
  extend(IndexPage.prototype, 'navItems', function (items: ItemList<Mithril.Children>) {
    const canView = app.forum.attribute('canViewRewind');
    const canModerate = app.forum.attribute('canModerateRewind');
    const enabled = app.forum.attribute('rewindEnabled');
    if (!(enabled && canView) && !canModerate) return;

    items.add(
      'rewind',
      LinkButton.component(
        {
          href: app.route('huseyinfiliz-rewind.forum'),
          icon: 'fas fa-history',
        },
        app.translator.trans('huseyinfiliz-rewind.forum.page.title')
      ),
      85
    );
  });
}
