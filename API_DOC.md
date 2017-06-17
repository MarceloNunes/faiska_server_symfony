Faiska - API Documentation
==========================

| Title           | Login/user authentication                           |
|-----------------|-----------------------------------------------------|
| URL             | `/login`                                            |
| URL Params      | None                                                |
| Data Params     | `username : [String]` <br/> `password: [String]`    |
| Sucess response | 200                                                 |
| Error response  | 400                                                 |

##### Success examples

Status: **200**

Output: 
```javascript
{
    "AUTH_KEY" : "46A36A35A3C58CDC8CCCCEEF67833B78"
}
```
##### Success examples

Status: **400**

Output: 
```javascript
{
    "field" : "password",
    "message" : "Invalid password"
}
```
