
| WARNING: This code has been written to provide feature not to look good. It is not recommended to use it as a code to learn! |
|------------------------------------------------------------------------------------------------------------------------------|

Quiz is built using Laravel and Vue and bootstrap

## How to restart game:
By calling SQL below:
```
TRUNCATE answers;
TRUNCATE nick;
UPDATE state SET VALUE =0 WHERE id='STEP';
```

## Rebuild Vue JavaScripts

`npm prod build`

## How to prepare questions and images

1. Fill questions in file: `app/Http/Controllers/Question.php`
2. Put images in `public/img` directory.
