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
docker run --rm --tty --interactive --volume ${PWD}:/app node:18 yarn --cwd /app install
```

Build for development:

```sh
docker run --rm --tty --interactive --volume ${PWD}:/app node:18 yarn --cwd /app watch
```

Build for production:

```sh
docker run --rm --tty --interactive --volume ${PWD}:/app node:18 yarn --cwd /app build
```

## Coding standards

This project follows coding standards.

Run

```sh
yarn run check-coding-standards
```

to check coding standards in `sccs` and `js` files.

### Scss

Run

```sh
yarn run check-coding-standards-scss
```

to check coding standards in `scss` files.

Some coding standards issues can be fixed automatically. Run

```sh
yarn run apply-coding-standards-scss
```

to apply any possible automatic fixes.

### Js

Run

```sh
yarn run check-coding-standards-js
```

to check coding standards in `js` files.

Some coding standards issues can be fixed automatically. Run

```sh
yarn run apply-coding-standards-js
```

to apply any possible automatic fixes.


### Twig

Check twig standards

```sh
docker compose exec phpfpm composer coding-standards-check/twig-cs-fixer
```

Apply twig standards

```sh
docker compose exec phpfpm composer coding-standards-apply/twig-cs-fixer
```
