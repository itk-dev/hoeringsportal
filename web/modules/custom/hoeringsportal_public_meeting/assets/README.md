# Høringsportal - Public meeting

We use [Webpack
Encore](http://symfony.com/doc/current/frontend.html#webpack-encore)
to handle frontend assets, see
http://symfony.com/doc/current/frontend.html#webpack-encore for
details.


JavaScript assets are put in `js/` and built assets are put in `build/`.

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

to check coding standards in `js` files.

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
