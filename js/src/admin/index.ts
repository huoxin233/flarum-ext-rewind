import app from 'flarum/admin/app';
import RewindSettingsPage from './components/RewindSettingsPage';

export { default as extend } from '../common/extend';

app.initializers.add('huseyinfiliz-rewind', () => {
  app.extensionData
    .for('huseyinfiliz-rewind')
    .registerPage(RewindSettingsPage)
    .registerPermission(
      {
        icon: 'fas fa-history',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.view_forum'),
        permission: 'huseyinfiliz-rewind.viewForum',
        allowGuest: true,
      },
      'view'
    )
    .registerPermission(
      {
        icon: 'fas fa-play-circle',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.generate'),
        permission: 'huseyinfiliz-rewind.generate',
      },
      'reply'
    )
    .registerPermission(
      {
        icon: 'fas fa-cog',
        label: app.translator.trans('huseyinfiliz-rewind.admin.permissions.moderate'),
        permission: 'huseyinfiliz-rewind.moderate',
      },
      'moderate'
    );
});
