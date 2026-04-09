import Extend from 'flarum/common/extenders';
import RewindSnapshot from './models/RewindSnapshot';
import CommunitySnapshot from './models/CommunitySnapshot';

export default [new Extend.Store().add('rw-snaps', RewindSnapshot).add('rw-community', CommunitySnapshot)];
