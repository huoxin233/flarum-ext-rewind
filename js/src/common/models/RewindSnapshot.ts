import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';

export default class RewindSnapshot extends Model {
  year = Model.attribute<number>('year');
  snapshotData = Model.attribute<Record<string, any>>('data');
  generatedAt = Model.attribute('generatedAt', Model.transformDate);
  isPublic = Model.attribute<boolean>('isPublic');
  canEdit = Model.attribute<boolean>('canEdit');
  canModerate = Model.attribute<boolean>('canModerate');
  isEmpty = Model.attribute<boolean>('isEmpty');

  user = Model.hasOne<User>('user');
}
