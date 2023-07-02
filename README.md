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
