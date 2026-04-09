import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import type { SaveSubmitEvent } from 'flarum/admin/components/AdminPage';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import Form from 'flarum/common/components/Form';
import GenerateModal from './GenerateModal';
import type { GenerateStep } from './GenerateModal';
import type Mithril from 'mithril';

type SlideSection = {
  key: string;
  icon: string;
  label: string;
  slides: string[];
};

const USER_SLIDE_SECTIONS: SlideSection[] = [
  {
    key: 'core',
    icon: 'fas fa-cube',
    label: 'huseyinfiliz-rewind.admin.settings.section_core',
    slides: ['post_count', 'discussion_count', 'active_days', 'word_count', 'most_active_month', 'top_words', 'night_owl'],
  },
  {
    key: 'content',
    icon: 'fas fa-trophy',
    label: 'huseyinfiliz-rewind.admin.settings.section_content',
    slides: ['best_post', 'top_emojis'],
  },
  {
    key: 'social',
    icon: 'fas fa-heart',
    label: 'huseyinfiliz-rewind.admin.settings.section_social',
    slides: ['top_tag', 'likes_given', 'likes_received', 'best_friend', 'best_friend_mentions', 'badges_earned', 'best_answers'],
  },
];

const COMMUNITY_SLIDE_SECTIONS: SlideSection[] = [
  {
    key: 'community_overview',
    icon: 'fas fa-cube',
    label: 'huseyinfiliz-rewind.admin.settings.section_core',
    slides: ['year-overview', 'busiest_month', 'peak_hour', 'total_words', 'new_members'],
  },
  {
    key: 'community_people',
    icon: 'fas fa-users',
    label: 'huseyinfiliz-rewind.admin.settings.section_people',
    slides: ['top_contributors', 'most_loved', 'most_active_user'],
  },
  {
    key: 'community_content',
    icon: 'fas fa-trophy',
    label: 'huseyinfiliz-rewind.admin.settings.section_content',
    slides: ['star_post', 'top_discussion', 'top_tag'],
  },
  {
    key: 'community_achievements',
    icon: 'fas fa-medal',
    label: 'huseyinfiliz-rewind.admin.settings.section_achievements',
    slides: ['best_answers_leaderboard', 'badge_leaderboard'],
  },
];

export default class RewindSettingsPage extends ExtensionPage {
  private activeTab: string = 'general';
  private slidesSubTab: string = 'user';
  private expandedSections: Set<string> = new Set(['core', 'community_overview']);

  saveSettings(e: SaveSubmitEvent) {
    const yearRaw = this.setting('huseyinfiliz-rewind.active_year')();
    const yearVal = Number(yearRaw);
    if (!yearVal || !Number.isInteger(yearVal) || yearVal < 2000 || yearVal > 2100) {
      app.alerts.show({ type: 'error' }, app.translator.trans('huseyinfiliz-rewind.admin.settings.invalid_active_year'));
      return Promise.reject();
    }
    return super.saveSettings(e);
  }

  content() {
    return (
      <div className="RewindSettings">
        <div className="RewindSettings-header">
          <div className="RewindSettings-tabs">
            {this.tabButton('general', 'fas fa-cog', 'huseyinfiliz-rewind.admin.tabs.general')}
            {this.tabButton('slides', 'fas fa-layer-group', 'huseyinfiliz-rewind.admin.tabs.slides')}
            {this.tabButton('advanced', 'fas fa-wrench', 'huseyinfiliz-rewind.admin.tabs.advanced')}
          </div>
        </div>
        <div className="RewindSettings-content">
          {this.activeTab === 'general' && this.generalTab()}
          {this.activeTab === 'slides' && this.slidesTab()}
          {this.activeTab === 'advanced' && this.advancedTab()}
        </div>
      </div>
    );
  }

  tabButton(tab: string, iconClass: string, labelKey: string): Mithril.Children {
    return (
      <Button
        className={'Button ' + (this.activeTab === tab ? 'Button--primary' : '')}
        icon={iconClass}
        onclick={() => {
          this.activeTab = tab;
        }}
      >
        {app.translator.trans(labelKey)}
      </Button>
    );
  }

  generalTab(): Mithril.Children {
    return (
      <Form>
        <div className="Form-group">
          {this.buildSettingComponent({
            type: 'boolean',
            setting: 'huseyinfiliz-rewind.enabled',
            label: app.translator.trans('huseyinfiliz-rewind.admin.settings.enabled_label'),
          })}
        </div>
        <div className="Form-group">
          <label>{app.translator.trans('huseyinfiliz-rewind.admin.settings.active_year_label')}</label>
          <input className="FormControl" type="number" min="2000" max="2100" step="1" bidi={this.setting('huseyinfiliz-rewind.active_year')} />
        </div>
        <div className="Form-group">
          {this.buildSettingComponent({
            type: 'boolean',
            setting: 'huseyinfiliz-rewind.show_menu_link',
            label: app.translator.trans('huseyinfiliz-rewind.admin.settings.show_menu_link_label'),
          })}
        </div>
        <div className="Form-group">
          {this.buildSettingComponent({
            type: 'boolean',
            setting: 'huseyinfiliz-rewind.community_comparison_enabled',
            label: app.translator.trans('huseyinfiliz-rewind.admin.settings.community_comparison_label'),
          })}
          <p className="helpText">{app.translator.trans('huseyinfiliz-rewind.admin.settings.community_comparison_help')}</p>
        </div>
        <div className="Form-group">{this.submitButton()}</div>
      </Form>
    );
  }

  slidesTab(): Mithril.Children {
    return (
      <Form>
        <div className="RewindSettings-metricsPills">
          <button
            className={'RewindSettings-pill' + (this.slidesSubTab === 'user' ? ' active' : '')}
            onclick={() => {
              this.slidesSubTab = 'user';
            }}
            type="button"
          >
            <i className="fas fa-user" /> {app.translator.trans('huseyinfiliz-rewind.admin.settings.slides_user')}
          </button>
          <button
            className={'RewindSettings-pill' + (this.slidesSubTab === 'community' ? ' active' : '')}
            onclick={() => {
              this.slidesSubTab = 'community';
            }}
            type="button"
          >
            <i className="fas fa-users" /> {app.translator.trans('huseyinfiliz-rewind.admin.settings.slides_community')}
          </button>
        </div>
        <p className="helpText">{app.translator.trans('huseyinfiliz-rewind.admin.settings.slides_help')}</p>
        {this.slidesSubTab === 'user' && this.renderSlideToggles(USER_SLIDE_SECTIONS, 'huseyinfiliz-rewind.hidden_user_slides', 'slide')}
        {this.slidesSubTab === 'community' &&
          this.renderSlideToggles(COMMUNITY_SLIDE_SECTIONS, 'huseyinfiliz-rewind.hidden_community_slides', 'community_slide')}
        <div className="Form-group">{this.submitButton()}</div>
      </Form>
    );
  }

  renderSlideToggles(sections: SlideSection[], settingKey: string, labelPrefix: string): Mithril.Children {
    const rawVal = this.setting(settingKey)();
    let hiddenSlides: string[] = [];
    try {
      hiddenSlides = JSON.parse(rawVal || '[]');
    } catch {
      hiddenSlides = [];
    }

    const toggle = (slide: string, visible: boolean) => {
      let current = [...hiddenSlides];
      if (visible) {
        current = current.filter((s) => s !== slide);
      } else {
        if (!current.includes(slide)) current.push(slide);
      }
      this.setting(settingKey)(JSON.stringify(current));
    };

    return (
      <div>
        {sections.map((section) => {
          const isExpanded = this.expandedSections.has(section.key);
          return (
            <div className={'RewindSettings-accordion' + (isExpanded ? ' RewindSettings-accordion--open' : '')} key={section.key}>
              <button className="RewindSettings-accordionHeader" type="button" onclick={() => this.toggleSection(section.key)}>
                <i className={section.icon + ' RewindSettings-accordionIcon'} />
                <span className="RewindSettings-accordionTitle">{app.translator.trans(section.label)}</span>
                <i className={'fas ' + (isExpanded ? 'fa-chevron-up' : 'fa-chevron-down') + ' RewindSettings-accordionChevron'} />
              </button>
              {isExpanded && (
                <div className="RewindSettings-accordionBody">
                  <div className="RewindSettings-metricsGrid">
                    {section.slides.map((slide) => (
                      <Switch key={slide} state={!hiddenSlides.includes(slide)} onchange={(val: boolean) => toggle(slide, val)}>
                        {app.translator.trans(`huseyinfiliz-rewind.admin.settings.${labelPrefix}_${slide}`)}
                      </Switch>
                    ))}
                  </div>
                </div>
              )}
            </div>
          );
        })}
      </div>
    );
  }

  toggleSection(key: string) {
    if (this.expandedSections.has(key)) {
      this.expandedSections.delete(key);
    } else {
      this.expandedSections.add(key);
    }
  }

  advancedTab(): Mithril.Children {
    return (
      <div>
        <div className="Form-group">
          <h3>{app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_community_title')}</h3>
          <p className="helpText">{app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_community_help')}</p>
          <Button className="Button Button--primary" icon="fas fa-magic" onclick={() => this.openCommunityGenerateModal()}>
            {app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_community_button')}
          </Button>
        </div>
        <hr />
        <div className="Form-group">
          <h3>{app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_users_title')}</h3>
          <p className="helpText">{app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_users_help')}</p>
          <Button className="Button Button--primary" icon="fas fa-users" onclick={() => this.openUserBatchGenerateModal()}>
            {app.translator.trans('huseyinfiliz-rewind.admin.settings.generate_users_button')}
          </Button>
        </div>
        <hr />
        <div className="Form-group">
          <h3>{app.translator.trans('huseyinfiliz-rewind.admin.settings.delete_rewinds_title')}</h3>
          <p className="helpText">{app.translator.trans('huseyinfiliz-rewind.admin.settings.delete_rewinds_help')}</p>
          <Button className="Button Button--primary" icon="fas fa-trash" onclick={() => this.openDeleteRewindsModal()}>
            {app.translator.trans('huseyinfiliz-rewind.admin.settings.delete_rewinds_button')}
          </Button>
        </div>
      </div>
    );
  }

  async loadGroupOptions(): Promise<Array<{ value: any; label: string }>> {
    const options: Array<{ value: any; label: string }> = [
      { value: '', label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.all_users') as string },
    ];
    try {
      const response = await app.request<{ groups: Array<{ id: number; namePlural: string; count: number }> }>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/rw-snaps/groups',
      });
      for (const g of response.groups || []) {
        options.push({ value: String(g.id), label: `${g.namePlural} (${g.count})` });
      }
    } catch (e) {
      console.error('Failed to load group options', e);
    }
    return options;
  }

  async loadYearOptions(): Promise<Array<{ value: any; label: string }>> {
    try {
      const response = await app.request<{ years: Array<{ year: number; count: number }> }>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/rw-snaps/year-stats',
      });
      return (response.years || []).map((y) => ({
        value: String(y.year),
        label: `${y.year} (${y.count} users)`,
      }));
    } catch (e) {
      console.error('Failed to load year options', e);
      return [];
    }
  }

  openCommunityGenerateModal() {
    const activeYear = parseInt(app.data.settings['huseyinfiliz-rewind.active_year'] as string, 10) || new Date().getFullYear();

    app.modal.show(GenerateModal, {
      title: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.community_title') as string,
      configFields: [
        {
          type: 'number' as const,
          key: 'year',
          label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.year_label') as string,
          value: activeYear,
        },
      ],
      loadSteps: async (): Promise<GenerateStep[]> => {
        const response = await app.request<{ steps: string[] }>({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/rw-community/generate-steps',
        });
        return (response.steps || []).map((key) => ({ key, label: key.replace(/_/g, ' ') }));
      },
      executeStep: async (step: GenerateStep, config: Record<string, any>) => {
        await app.request({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/rw-community/generate-step',
          body: { step: step.key, year: config.year },
        });
      },
    });
  }

  openUserBatchGenerateModal() {
    const activeYear = parseInt(app.data.settings['huseyinfiliz-rewind.active_year'] as string, 10) || new Date().getFullYear();

    app.modal.show(GenerateModal, {
      title: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.users_title') as string,
      configFields: [
        {
          type: 'number' as const,
          key: 'year',
          label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.year_label') as string,
          value: activeYear,
        },
        {
          type: 'select' as const,
          key: 'group',
          label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.group_label') as string,
          value: '',
          loadOptions: () => this.loadGroupOptions(),
        },
      ],
      loadSteps: async (config: Record<string, any>): Promise<GenerateStep[]> => {
        const body: Record<string, any> = { year: config.year };
        if (config.group) body.group = parseInt(config.group, 10);
        const response = await app.request<{ users: Array<{ id: number; username: string }> }>({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/rw-snaps/missing-users',
          body,
        });
        return (response.users || []).map((u) => ({ key: String(u.id), label: u.username }));
      },
      executeStep: async (step: GenerateStep, config: Record<string, any>) => {
        await app.request({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/rw-snaps/generate-for-user',
          body: { userId: parseInt(step.key, 10), year: config.year },
        });
      },
    });
  }

  openDeleteRewindsModal() {
    app.modal.show(GenerateModal, {
      title: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.delete_title') as string,
      confirmMode: true,
      confirmLabel: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.delete_confirm') as string,
      configFields: [
        {
          type: 'select' as const,
          key: 'year',
          label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.year_label') as string,
          value: '',
          loadOptions: () => this.loadYearOptions(),
        },
        {
          type: 'select' as const,
          key: 'group',
          label: app.translator.trans('huseyinfiliz-rewind.admin.generate_modal.group_label') as string,
          value: '',
          loadOptions: () => this.loadGroupOptions(),
        },
      ],
      loadSteps: async () => [],
      executeStep: async () => {},
      onConfirm: async (config: Record<string, any>) => {
        const response = await app.request<{ deleted: number }>({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/rw-snaps/batch-delete',
          body: { year: parseInt(config.year, 10), group: config.group ? parseInt(config.group, 10) : null },
        });
        return response.deleted;
      },
    });
  }
}
