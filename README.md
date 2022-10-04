## Junior Fullstack - Coding Challenge


### Introduction

The purpose of this challenge is to evaluate your coding skills,
your understanding of fullstack solutions,
and your ability to learn and adapt to unfamiliar tech stacks.

Please deliver your code within one week.

We will evaluate:
- How clean, well-structured and easy to understand is your code.
- If your code is well optimised (runs fast, no memory issues...).
- If your code has no security issues.
- If you deliver the required features.
- How creative is your solution.
- How good-looking is your UI.
- If you respected all the constraints described below.


### How to prepare your dev environment

You will need installed in your computer:
- git
- docker and docker-compose
- composer

Once you have those installed. Please follow these steps:

1) Using git, clone this repository in your computer.
2) Go to the `challenge` subdirectory and run composer:
```bash
$ composer install
```
3) Go back the root directory and run docker-compose:
```bash
$ docker-compose up -d
```
4) Wait until docker downloads and runs all the containers (might take a few minutes).
5) Test that the web server works by visiting the URL: http://localhost:8087
6) Test that the API works by visiting the URL: http://localhost:8087/api/hello/world
7) Now you can start coding. The main and only files you ought to modify are `public/index.html` and `public/api/index.php`. 
8) Do not forget to do commits periodically and frequently, so we can better understand your thought process.
9) When you are done, please push your changes to a new repository and share it with us, so we can review your solution.

Find the challenge requirements below.


### The challenge: Reading progress... in Singapore

A Singapore based CEO wants you to improve their blog.
The CEO wants you to do 3 things:
- Improve the looks of the post page.
- Create a reading progress bar in the post page.
- When the user is done reading, show to the user:
  - The time when the user started reading the post.
  - The time when the user finished reading the post.
  - The amount of time in hours, minutes and seconds that took the user to finish reading the post.

All the user-facing dates must be displayed in the Singaporean timezone.


#### Frontend exercise constraints:

In order to make the post page looks good and to add the progress bar...

You are free to reproduce any design of any blog article pages that you like
(for example: [Medium](https://medium.com/words-for-life/dear-people-who-hate-clapping-8497747199e6)).

You are free to add any fonts, JS, CSS libraries or frameworks within `public/index.html`,
but you are not allowed to use node/npm, neither any sort of transpiler nor bundler.
You are not allowed to "upload" any static resources to the server.

The html structure of the post content itself (paragraphs, titles...) must not be modified.


#### Backend exercise constraints:

In order to keep track of the user reading time...

You are free to add any amount of code and endpoints to the API in `public/api/index.php`,
and to send any amount of requests from `public/index.html`,
but you are not allowed to add additional composer packages nor to call any external services.

Notice that the solution should work even if the user decides to close their browser and continue reading later,
in order to accomplish that you will probably need to use redis or cookies or both. You have an example of 
how to use both these services in `public/api/index.php`.

Even that redis is an in-memory database, you are free to use it as if it was a traditional one
(i.e. for simplification purposes, you can safely assume the redis server will never be restarted,
there is no key eviction mechanism, and so on).


#### A few clues...

Your users are expected to access the post page by visiting the URL:
http://localhost:8087

They are expected to start reading, to close the browser and return to keep reading later.

In order to call the API from the UI, you may use the base URL:
http://localhost:8087/api/

A quick example using vanilla javascript (notice that this code exposes our app
to a XSS attack, so it might not be the best way to do it):

```html
<div id="main">Loading...</div>
<script type="text/javascript">
    fetch('http://localhost:8087/api/hello/world')
        .then((res) => res.json())
        .then((data) => {
            const el = document.getElementById('main');
            if (el) {
                el.innerHTML = `Hello ${data.name}`;
            }
        }).catch(function (err) {
            console.error('Something went wrong.', err);
        });
</script>
```

If you want to add a JS library or framework to your UI, you could import it using a CDN or similar.
For example, in order to include React in `index.html` you could use:

```html
<script src="https://unpkg.com/react@15/dist/react.js"></script>
<script src="https://unpkg.com/react-dom@15/dist/react-dom.js"></script>
```

Notice that, because you are not allowed to use bundlers or transpilers,
you might be limited in terms of which features of the framework you can use.
For example, in the case of React you won't be able to use JSX.
