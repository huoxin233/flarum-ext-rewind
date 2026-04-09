![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/huseyinfiliz/rewind.svg)](https://packagist.org/packages/huseyinfiliz/rewind) [![Total Downloads](https://img.shields.io/packagist/dt/huseyinfiliz/rewind.svg)](https://packagist.org/packages/huseyinfiliz/rewind)

A year-in-review extension for [Flarum](https://flarum.org "null") forums. Allow your community and users to look back at their yearly statistics, top posts, and community milestones with beautiful animations and a personalized slideshow interface.

### 🎨 Personalized Rewind Slideshow
![](https://cdn.discuss.flarum.org/2026-04-03/1775222980-382465-image-thumb.webp)

### 📊 Community Snapshot
![](https://cdn.discuss.flarum.org/2026-04-03/1775223067-536817-image-thumb.webp)

### ⚙️ Admin Settings & Generation
![](https://cdn.discuss.flarum.org/2026-04-03/1775223436-229143-image-thumb.webp)

## Features
* 📊 **Community Snapshot**: Overview of new users, top discussions, and busiest months.
* 👤 **Personalized Rewind**: Individual metrics like post count, best posts, word counts, and night-owl statistics.
* 🎨 **Slideshow UI**: Beautiful, animated slideshow interface for users to experience their year.
* 🔌 **Ecosystem Integration**: Automatically fetches optional metrics if compatible extensions are enabled.
* 📱 **Responsive Design**: Optimized layout for mobile, tablet, and desktop viewing.

## Installation
```
composer require huseyinfiliz/rewind:"*"
```

After installing, run the following commands to apply the database changes and clear the cache:
```
php flarum migrate
php flarum cache:clear
```

## Updating
```
composer update huseyinfiliz/rewind:"*"
php flarum migrate
php flarum cache:clear
```

To remove simply run `composer remove huseyinfiliz/rewind`.

## Quick Start

### For Users
1. Users can access their personal rewind from their user profile.
2. The community snapshot is available at Rewind page.

### For Admins
1. Enable the **Rewind** extension in your admin panel.
2. In the extension settings, select the year you want to generate a Rewind for (e.g., `2025`).
3. Click the **Generate** button. (This process may take a few minutes depending on the size of your forum).

> **Important Note**: Statistics for a past year are designed to be generated after that year has ended (e.g., generating 2025 data in January 2026).

## Optional Integrations
The rewind automatically integrates with these extensions when they are enabled:

| Extension          | Metrics                                                                 |
|--------------------|-------------------------------------------------------------------------|
| flarum/likes       | Most loved post, likes received/given, best friend                      |
| flarum/mentions    | Most mentioned user, user who mentioned you the most                    |
| fof/badges         | Badges earned during the year, badge leaderboard                        |
| fof/best-answer    | Best answers count, best answers leaderboard                            |
| flarum/tags        | Top community tag, user's top tag                                       |
| fof/reactions      | Total community reactions                                               |
| flarum/emoji       | CDN mirror address for emojis used in slides                            |

No configuration is needed — install the extension and the metrics will be collected automatically during the generation process.

## 🌍 Translations
This extension comes with English translations. Community translations are welcome!

## 💖 Support & Contributing
If you find this extension useful, consider:

* ⭐ Starring the repository on GitHub
* 🌐 Contributing translations

## Links

* [Discuss](https://discuss.flarum.org/d/38832)
* [Packagist](https://packagist.org/packages/huseyinfiliz/rewind)
* [GitHub](https://github.com/huseyinfiliz/rewind)
* [Issues](https://github.com/huseyinfiliz/rewind/issues)