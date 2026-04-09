import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addSidebarLink() {
  extend('flarum/forum/components/IndexSidebar', 'navItems', function (items) {
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
