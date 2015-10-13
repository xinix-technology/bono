---
layout: index
subtitle: <strong><a target="_BLANK" href="http://xinix.co.id/p/product">Bono</a></strong> is PHP Development Framework Base On <strong><a target="_BLANK" href="http://www.slimframework.com">Slim Framework</a></strong>
---

### Philosophy

The main goal of "pas" is to make sure application developers life easier. There are many tools outside there, generators, build tools, automation tools, and package management. Many of them are interoperable to each others. But using too many tools on your belt for developing application is not easy. Not for my friends I've known. 

If you working with javascript client side, node.js, php, or other platform. You will see each of them have their own thing to accomplished their job. Bower for client side javascript, npm for node.js, composer for php, maven for java, you named it! Unfortunately for many of us have to deal with more than one kind. You will not be web developer if you don't do javascript and php or other server side programming. Sometimes my colleagues get confused and tend to afraid to use another new tools.

"pas" is (another) package management and automation. We use it regularly at our organization, Xinix Technology. The aim of this tool is to help developers to manage their work cycle of applications that they build. 

We want developers able getting started using "pas" intuitively, as the same approach as using npm, composer, etc.

Todays, developers can use "pas" to help them at php. But in a very short time we can expect that it will be usable for many platform and programming languages. Nowadays we used "pas" internally as an alternative of composer on php developments.

Todays, Several tasks that will be easier by using "pas" are:

- Project initialization with plain archetype from github
- Project initialization from your earlier works.
- One command call to manage library dependencies for php (replacing composer).
- Custom automation tasks provided by plugins
  
### How to Install

You need node.js to install "pas". If you already have node.js and npm in your system, execute:

```
npm install -g pas
```

As it will be installing "pas" globally, you might have to execute as root or using sudo.

### How to Initialize New Project

Archetype is a scaffolding concept to use your earlier work or single package as your base project. Using archetype to initialize new project will get rid repetitive tedious works.  

```
pas init [archetype-name] [directory-name]
```

example,

```
pas init reekoheek/bono-arch a-new-web-app
```

You can read further from <a href="docs/index.html">documentation</a>.