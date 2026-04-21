<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-color: #f5f5f5;
    --text-color: #333;
}

@media (prefers-color-scheme: dark) {
    :root {
        --bg-color: #1a1a1a;
        --text-color: #e0e0e0;
    }
}

/* Manual overrides — these win regardless of browser pref */
[data-theme="light"] {
  --bg-color: #f5f5f5;
  --text-color: #333;
}

[data-theme="dark"] {
  --bg-color: #1a1a1a;
  --text-color: #e0e0e0;
}

/* TBD: Implement data structure for light/dark theme override */

body {
    font-family: 'IBM Plex Mono', monospace;
    line-height: 1.6;
    color: var(--text-color);
    background: var(--bg-color);
}

a {
    color: var(--text-color);
    text-decoration: underline;
}

/* Special links that are underlined only on hover */
.site-name a,
.lang-toggle a,
.nav-menu a,
.portfolio-title a {
    text-decoration: none;
}

.site-name a:hover,
.lang-toggle a:hover,
.nav-menu a:hover,
.portfolio-title:hover {
    text-decoration: underline;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    display: grid;
    grid-template-columns: 20px 5px 2fr 0.2fr 50px 1fr;
    column-gap: 10px;
    row-gap: 10px;
    background: var(--bg-color);
}

.header {
    grid-column: 1 / 6;
    display: flex;
    align-items: center;
    padding: 20px 0;
}

.menu-icon {
    grid-column: 1 / 2;
    font-size: 24px;
    cursor: pointer;
    margin-right: 20px;
    transition: opacity 0.2s;
}

.menu-icon:hover {
    opacity: 0.7;
}

.nav-menu {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: var(--bg-color);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 1000;
    padding: 80px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.nav-menu.open {
    opacity: 1;
    visibility: visible;
}

.nav-menu-close {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 30px;
    cursor: pointer;
    background: none;
    border: none;
    color: var(--text-color);
}

.nav-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: center;
}

.nav-menu li {
    margin-bottom: 30px;
}

.nav-menu a {
    color: var(--text-color);
    font-size: 32px;
    transition: opacity 0.2s;
}

.nav-menu a:hover {
    opacity: 0.7;
}

.menu-overlay {
    display: none;
}

.site-name {
    font-size: 18px;
    font-weight: normal;
}

.header-right {
    grid-column: 6 / 7;
    text-align: right;
    padding: 20px 0;
}

.lang-toggle {
    font-size: 14px;
    margin-bottom: 10px;
}

.content {
    grid-column: 3 / 5;
}

.content h1 {
    font-size: 32px;
    font-weight: 400;
    margin: 40px 0 30px 0;
}

.content h2 {
    font-size: 18px;
    margin: 40px 0 15px 0;
}

.content p {
    margin-bottom: 20px;
    text-align: left;
}

.content ul {
    list-style-type: circle;
}

.content ol {
    list-style-type: decimal-leading-zero;
}

.image-container {
    grid-column: 1 / 7;
    margin: 30px 0;
    display: grid;
    grid-template-columns: subgrid;
    position: relative;
}

.image-container.small {
    grid-column: 1 / 6;
}

.image-container img {
    grid-column: 1 / -1;
    width: 100%;
    height: auto;
    display: block;
}

.image-container.small img {
    width: 70%;
}

.image-caption {
    font-size: 14px;
    line-height: 1.8;
    width: fit-content;
    max-width: 400px;
    margin-top: -30px;
    margin-bottom: 10px;
}

.image-caption span {
    background: var(--bg-color);
    display: inline;
    padding: 6px 15px;
    box-decoration-break: clone;
    -webkit-box-decoration-break: clone;
}

.image-container.small .image-caption {
    grid-column: 2 / 5;
    justify-self: end;
    text-align: right;
    position: absolute;
    top: 50px;
}

.image-container:not(.small) .image-caption {
    grid-column: 3 / 5;
    justify-self: start;
    text-align: left;
    position: absolute;
    bottom: 10px;
}

.blockquote-container {
    grid-column: 1 / 4;
    margin: 30px 0;
}

.blockquote-container blockquote {
    font-size: 18px;
    font-style: italic;
    color: var(--text-color);
    max-width: 500px;
}

.portfolio-grid {
    grid-column: 1 / 7;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 30px 0;
}

.portfolio-grid.small {
    grid-column: 3 / 7;
}

.portfolio-item {
    position: relative;
    overflow: hidden;
    display: block;
    text-decoration: none;
}

.portfolio-item img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.portfolio-item:hover img {
    transform: scale(1.05);
}

.portfolio-title {
    position: absolute;
    bottom: 10px;
    left: 10px;
    font-size: 14px;
    line-height: 1.8;
    width: fit-content;
    max-width: calc(100% - 20px);
}

.portfolio-title span {
    background: var(--bg-color);
    display: inline;
    padding: 6px 15px;
    box-decoration-break: clone;
    -webkit-box-decoration-break: clone;
}

.sidebar {
    grid-column: 6 / 7;
}

.sidebar-image {
    margin: 30px 0;
}

.sidebar-image img {
    width: 100%;
    height: auto;
    display: block;
}

.sidebar-text {
    text-align: right;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-color);
    margin: 20px 0;
}

.footer {
    grid-column: 1 / 7;
    padding: 30px 0 20px 0;
    margin-top: 40px;
    font-size: 14px;
    color: var(--text-color);
}

/* Blog listing styles */
.blog-item {
    grid-column: 3 / 5;
    margin-bottom: 40px;
    border-bottom: 1px solid var(--text-color);
    padding-bottom: 20px;
}

.blog-item h2 {
    font-size: 24px;
    font-weight: 400;
    margin-bottom: 10px;
}

.blog-item h2 a {
    text-decoration: none;
}

.blog-item h2 a:hover {
    text-decoration: underline;
}

.blog-meta {
    font-size: 14px;
    margin-bottom: 15px;
    opacity: 0.7;
}

.blog-excerpt {
    margin-bottom: 15px;
}


/* Begynner ny socials-seksjon - under utprøving */

.social-links {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0;

  color: var(--text-color);
}

.social-links a {
  display: inline-flex;
  width: 24px;
  height: 24px;
  color: inherit;
  text-decoration: none;
}

.social-links svg {
  width: 100%;
  height: 100%;
  fill: currentColor;        /* arver .social-links color */
  transition: transform 0.3s ease;
}

.social-links a:hover svg,
.social-links a:focus svg {
  transform: scale(1.1);
  transform: rotate(20deg);
}


/* Slutt på socials-seksjon */

/* Ny kode for lys/mørk toggle */

#theme-toggle {
  padding: 0;
  width: 24px;
  height: 24px;
  display: inline-flex;
  background: none;
  border: none;
}

.sun-parts { display: none; }
.moon-part { display: block; }

[data-theme="dark"] .sun-parts { display: block; }
[data-theme="dark"] .moon-part { display: none; }

@media (prefers-color-scheme: dark) {
  :root:not([data-theme]) .sun-parts { display: block; }
  :root:not([data-theme]) .moon-part { display: none; }
}

/* Slutt på toggle-kode */


@media (max-width: 720px) {
    .container {
        display: grid;
        grid-template-columns: 15px 1fr 2fr 1fr 1fr 15px;
        column-gap: 5px;
        row-gap: 10px;
        padding: 20px 0;
        background: var(--bg-color);
    }

    .header,
    .header-right,
    .content,
    .footer,
    .blog-item {
        grid-column: 2 / 6;
    }

    .image-container {
        grid-column: 1 / 7;
    }

    .image-container.small {
        grid-column: 2 / 6;
    }

    .sidebar {
        grid-column: 2 / 6;
    }

    .blockquote-container {
        grid-column: 2 / 6;
        text-align: right;
    }

    .header-right {
        text-align: left;
        border-top: none;
    }

    /* Mobile: captions positioned absolutely instead of grid */
    .image-container {
        display: block;
        position: relative;
    }

    .image-caption {
        position: absolute;
        bottom: 10px;
        left: 10px;
        margin-top: 0;
        margin-bottom: 0;
    }

    .image-container.small .image-caption {
        position: static;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .image-container.small img {
        width: 100%;
    }

    .portfolio-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .portfolio-grid.small {
        grid-column: 1 / 6;
    }

    .sidebar-image img {
        width: 70%;
        margin-left: auto;
    }
}
</style>