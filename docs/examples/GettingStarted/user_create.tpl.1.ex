<form metod="post" action="{url_for controller="user" action="create"}"><br/>
    {errors for='user' prop='first_name'}<br/>
    first name: {form_field for='user' prop='first_name'}<br/>
    {errors for='user' prop='last_name'}<br/>
    last name: {form_field for='user' prop='last_name'}<br/>
    <input type="submit"></form>
