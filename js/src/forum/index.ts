import app from 'flarum/forum/app';
import addProfileTab from './addProfileTab';
import addSidebarLink from './addSidebarLink';
import addUserMenuLink from './addUserMenuLink';

export { default as extend } from './extend';

app.initializers.add('huseyinfiliz-rewind', () => {
  addProfileTab();
  addSidebarLink();
  addUserMenuLink();
});
