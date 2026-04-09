import Model from 'flarum/common/Model';

export default class CommunitySnapshot extends Model {
  year() {
    return Model.attribute<number>('year').call(this);
  }

  snapshotData() {
    return Model.attribute<Record<string, any>>('data').call(this);
  }

  generatedAt() {
    return Model.attribute('generatedAt', Model.transformDate).call(this);
  }
}
