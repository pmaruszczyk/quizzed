

## How to restart game:
By calling SQL below:
```
TRUNCATE answers;
TRUNCATE nick;
UPDATE state SET VALUE =0 WHERE id='STEP';
```

Quiz is build using Laravel and Vue and bootstrap
