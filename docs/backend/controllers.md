# Controllers Overview

Krayin Controllers are meant to be thin. Their primary responsibilities are:
1. **Authorization**: Checking if the user can perform the action.
2. **Validation**: Passing the incoming request to a FormRequest class.
3. **Delegation**: Passing the validated data array to a Repository.
4. **Response**: Returning a Blade view, a Redirect, or a JSON response.

Example (Simplified):
```php
public function store(LeadFormRequest $request, LeadRepository $repo) {
    $repo->create($request->all());
    return redirect()->route('admin.leads.index')->with('success', 'Lead created!');
}
```
