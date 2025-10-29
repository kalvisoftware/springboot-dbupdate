# Small Notes API (PHP)

This folder contains a minimal file-backed Notes API implemented in plain PHP (no frameworks). It's useful for quick local testing and demos.

Files:
- `index.php` - the API server
- `data/notes.json` - the JSON file used as a simple data store

Run locally (PowerShell on Windows):

```powershell
# from repository root
php -S localhost:8000 -t api
```

Try requests (PowerShell examples):

List notes:
```powershell
Invoke-RestMethod -Uri http://localhost:8000/notes -Method GET
```

Create a note:
```powershell
Invoke-RestMethod -Uri http://localhost:8000/notes -Method POST -ContentType 'application/json' -Body (@{title='My note'; content='Hello'} | ConvertTo-Json)
```

Get a note:
```powershell
Invoke-RestMethod -Uri http://localhost:8000/notes/1 -Method GET
```

Update a note:
```powershell
Invoke-RestMethod -Uri http://localhost:8000/notes/1 -Method PUT -ContentType 'application/json' -Body (@{title='Updated'} | ConvertTo-Json)
```

Delete a note:
```powershell
Invoke-RestMethod -Uri http://localhost:8000/notes/1 -Method DELETE
```
