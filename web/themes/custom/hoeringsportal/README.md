# Høringsportalen

We use [Webpack
Encore](http://symfony.com/doc/current/frontend.html#webpack-encore)
to handle frontend assets, see
http://symfony.com/doc/current/frontend.html#webpack-encore for
details.


JavaScript and CSS (actually SCSS) assets are put in `assets/js/` and
`assets/css/`, respectively, and built assets are put in `build/`.


## Building assets

First, install tools and requirements:

```sh
npm config set engine-strict true
npm install
```

Build for development:

```
./node_modules/.bin/encore dev --watch
```

Build for production:

```
./node_modules/.bin/encore production
```

## Coding standards

This project follows coding standards.

Run

```sh
npm run check-coding-standards
```

to check coding standards in `sccs` and `js` files.

### Scss

Run

```sh
npm run check-coding-standards-scss
```

to check coding standards in `scss` files.

Some coding standards issues can be fixed automatically. Run

```sh
npm run apply-coding-standards-scss
```

to apply any possible automatic fixes.

### Js

Run

```sh
npm run check-coding-standards-js
```

to check coding standards in `js` files.

Some coding standards issues can be fixed automatically. Run

```sh
npm run apply-coding-standards-js
```

to apply any possible automatic fixes.
