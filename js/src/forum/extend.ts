import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import ForumRewindPage from './components/ForumRewindPage';
import ProfileRewindPage from './components/ProfileRewindPage';
import SlideshowPage from './components/SlideshowPage';
import CommunitySlideshow from './components/CommunitySlideshow';

export default [
  ...commonExtend,

  new Extend.Routes()
    .add('huseyinfiliz-rewind.forum', '/rewind', ForumRewindPage)
    .add('huseyinfiliz-rewind.profile', '/u/:username/rewind', ProfileRewindPage)
    .add('huseyinfiliz-rewind.slideshow', '/rewind/view/:id/:year', SlideshowPage)
    .add('huseyinfiliz-rewind.community-slideshow', '/rewind/view/:year', CommunitySlideshow)
    .add('huseyinfiliz-rewind.community-slideshow-default', '/rewind/view', CommunitySlideshow),
];
