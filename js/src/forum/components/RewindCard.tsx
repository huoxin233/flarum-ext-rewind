import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Link from 'flarum/common/components/Link';
import type Mithril from 'mithril';

const MONTH_NAMES = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

interface RewindCardAttrs {
  metricKey: string;
  data: Record<string, any>;
  index?: number;
}

function getCountUpValue(el: Element, target: number, duration: number = 600) {
  const start = performance.now();
  const update = (now: number) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.round(target * eased).toLocaleString();
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

function getTier(value: number, thresholds: number[]): number {
  for (let i = thresholds.length - 1; i >= 0; i--) {
    if (value >= thresholds[i]) return i + 1;
  }
  return 1;
}

function getContextualMessage(key: string, data: Record<string, any>): string | null {
  const count = data.count ?? data.interaction_count ?? data.mention_count ?? 0;
  if (count <= 0) return null;

  const tierKey = (k: string, tiers: number[]) => {
    const tier = getTier(count, tiers);
    const msg = app.translator.trans(`huseyinfiliz-rewind.forum.metrics.${k}.messages.tier_${tier}`);
    return typeof msg === 'string' ? msg : Array.isArray(msg) ? msg.join('') : null;
  };

  switch (key) {
    case 'post_count':
      return tierKey('post_count', [1, 11, 51, 201, 501]);
    case 'active_days':
      return tierKey('active_days', [1, 6, 21, 51, 101]);
    case 'word_count':
      return tierKey('word_count', [1, 501, 2001, 10001, 50001]);
    case 'best_answers':
      return tierKey('best_answers', [1, 2, 6]);
    case 'best_friend':
      if (!data.user_id) return null;
      const msg = app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_friend.message');
      return typeof msg === 'string' ? msg : null;
    default:
      return null;
  }
}

const COUNT_METRICS = ['post_count', 'discussion_count', 'active_days', 'word_count', 'likes_given', 'likes_received', 'best_answers'];

export default class RewindCard extends Component<RewindCardAttrs> {
  view(): Mithril.Children {
    const { metricKey, data, index = 0 } = this.attrs;
    const message = getContextualMessage(metricKey, data);

    return (
      <div className="RewindCard" style={{ animationDelay: `${index * 0.08}s` }}>
        <div className="RewindCard-header">
          <h3>{app.translator.trans(`huseyinfiliz-rewind.forum.metrics.${metricKey}.title`)}</h3>
        </div>
        <div className="RewindCard-body">
          {this.renderMetric(metricKey, data)}
          {message && <p className="RewindCard-message">{message}</p>}
        </div>
      </div>
    );
  }

  oncreate(vnode: Mithril.VnodeDOM) {
    super.oncreate(vnode);

    const { metricKey, data } = this.attrs;
    if (COUNT_METRICS.includes(metricKey) && data.count > 0) {
      const countEl = (vnode.dom as Element).querySelector('.RewindCard-countUp');
      if (countEl) {
        getCountUpValue(countEl, data.count);
      }
    }
  }

  renderMetric(key: string, data: Record<string, any>): Mithril.Children {
    switch (key) {
      case 'post_count':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.post_count.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'discussion_count':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.discussion_count.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'active_days':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.active_days.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'top_tag':
        if (!data.tag_name) {
          return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.top_tag.no_data')}</p>;
        }
        return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.top_tag.description', { tag_name: data.tag_name, count: data.count })}</p>;

      case 'most_active_month':
        return (
          <div>
            <p>
              {app.translator.trans('huseyinfiliz-rewind.forum.metrics.most_active_month.description', {
                month: MONTH_NAMES[data.peak_month] || '',
                count: data.peak_count,
              })}
            </p>
            <div className="RewindCard-chart">
              {Object.entries(data.months || {}).map(([month, count]) => (
                <div className="RewindCard-chartBar" key={month}>
                  <div
                    className="RewindCard-chartBarFill"
                    style={{ height: data.peak_count ? `${((count as number) / data.peak_count) * 100}%` : '0%' }}
                  />
                  <span className="RewindCard-chartLabel">{MONTH_NAMES[parseInt(month)]?.substring(0, 3)}</span>
                </div>
              ))}
            </div>
          </div>
        );

      case 'word_count':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.word_count.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'best_post':
        if (!data.post_id) {
          return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_post.no_data')}</p>;
        }
        if (data.metric_type === 'likes') {
          return (
            <p>
              {app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_post.description_likes', {
                count: data.count,
                discussion_title: data.discussion_title,
              })}
            </p>
          );
        }
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_post.description_replies', {
              count: data.count,
              discussion_title: data.discussion_title,
            })}
          </p>
        );

      case 'likes_given':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.likes_given.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'likes_received':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.likes_received.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      case 'best_friend':
        if (!data.user_id) {
          return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_friend.no_data')}</p>;
        }
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_friend.description', {
              display_name: <Link href={app.route('user', { username: data.username })}>{data.display_name}</Link>,
              count: data.interaction_count,
            })}
          </p>
        );

      case 'best_friend_mentions':
        if (!data.user_id) {
          return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_friend_mentions.no_data')}</p>;
        }
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_friend_mentions.description', {
              display_name: <Link href={app.route('user', { username: data.username })}>{data.display_name}</Link>,
              count: data.mention_count,
            })}
          </p>
        );

      case 'badges_earned':
        if (!data.count) {
          return <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.badges_earned.no_data')}</p>;
        }
        return (
          <div>
            <p>{app.translator.trans('huseyinfiliz-rewind.forum.metrics.badges_earned.description', { count: data.count })}</p>
            {data.badges && data.badges.length > 0 && (
              <ul className="RewindCard-badgeList">
                {data.badges.map((badge: any, i: number) => (
                  <li key={i}>
                    {badge.icon && <i className={badge.icon} />} {badge.name}
                  </li>
                ))}
              </ul>
            )}
          </div>
        );

      case 'best_answers':
        return (
          <p>
            {app.translator.trans('huseyinfiliz-rewind.forum.metrics.best_answers.description', {
              count: <strong className="RewindCard-countUp">0</strong>,
            })}
          </p>
        );

      default:
        return <p>{JSON.stringify(data)}</p>;
    }
  }
}
