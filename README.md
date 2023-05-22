# Event API

## What does this plugin do?
- This plugin registers the custom post type Event.
- It also registers the taxonomy Category which is associated with Event post type.
- It provides handlful of APIs to perform CRUD operations for the Event post type.
- Only Admin can use the APIs.

## The details about the Event APIs:
- Event Create API:
  - Use the URL: http://your_domain/wp-json/storeapps/v1/events/create
  - You can pass the following data along with the request:
  - title: string
  - description: string
  - start_date_time: string in the format of dd/mm/yyyy hh:mm
  - end_date_time: string in the format od dd/mm/yyyy hh:mm
  - category_slugs: string containing the comma-separated slugs of the categories

- Event Update API:
  - Use the URL: http://your_domain/wp-json/storeapps/v1/events/update
  - You can pass the following data along with the request:
  - title: string
  - description: string
  - start_date_time: string in the format of dd/mm/yyyy hh:mm
  - end_date_time: string in the format od dd/mm/yyyy hh:mm
  - category_slugs: string containing the comma-separated slugs of the categories

- Event Fetch API by Event ID
  - Use the URL: http://your_domain/wp-json/storeapps/v1/events/show
  - You must pass the following data along with the request:
  - id: string containing the integer value

- Event Fetch API by Event Start Date
  - Use the URL: http://your_domain/wp-json/storeapps/v1/events/list
  - You must pass the following data along with the request:
  - start_date: string in the format of dd/mm/yyyy

- Event Delete API:
  - Use the URL: http://your_domain/wp-json/storeapps/v1/events/delete
  - You must pass the following data along with the request:
  - id: string containing the integer value

## What is way to Authenticate when using the API:
To authenticate yourself, you can use the Application Password feature available in WordPress. You need to visit the user edit page and add the Application Passwords for the user. The credentials can be passed along to REST API requests server using Basic Auth. For more details on Application Passwords, visit https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/.
When using the API, you must make sure you are using Admin credentials to authenticate yourself. Only admin can access the APIs.
