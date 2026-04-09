import Model from 'flarum/common/Model';

export default class RewindSnapshot extends Model {
  year() {
    return Model.attribute<number>('year').call(this);
  }

  snapshotData() {
    return Model.attribute<Record<string, any>>('data').call(this);
  }

  generatedAt() {
    return Model.attribute('generatedAt', Model.transformDate).call(this);
  }

  isPublic() {
    return Model.attribute<boolean>('isPublic').call(this);
  }

  canEdit() {
    return Model.attribute<boolean>('canEdit').call(this);
  }

  canModerate() {
    return Model.attribute<boolean>('canModerate').call(this);
  }

  isEmpty() {
    return Model.attribute<boolean>('isEmpty').call(this);
  }

  user() {
    return Model.hasOne('user').call(this);
  }
}
