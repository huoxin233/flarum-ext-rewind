import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import RewindSettingsPage from './components/RewindSettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin()
    .page(RewindSettingsPage)
    .permission(
      () => ({
        icon: 'fas fa-history',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.view_forum'),
        permission: 'huseyinfiliz-rewind.viewForum',
        allowGuest: true,
      }),
      'view'
    )
    .permission(
      () => ({
        icon: 'fas fa-play-circle',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.generate'),
        permission: 'huseyinfiliz-rewind.generate',
      }),
      'reply'
    )
    .permission(
      () => ({
        icon: 'fas fa-cog',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.moderate'),
        permission: 'huseyinfiliz-rewind.moderate',
      }),
      'moderate'
    ),
];
