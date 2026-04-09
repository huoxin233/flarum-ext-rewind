import Model from 'flarum/common/Model';

export default class CommunitySnapshot extends Model {
  year = Model.attribute<number>('year');
  snapshotData = Model.attribute<Record<string, any>>('data');
  generatedAt = Model.attribute('generatedAt', Model.transformDate);
}
