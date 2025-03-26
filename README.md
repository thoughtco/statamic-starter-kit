# Starter Kit
The starter kit is an attempt give you the additional extras TC run on top of a Statamic install.  If you feel something else which is used on a regular basis should be installed, let us know and we can have a look.

### Install

On the blank Statamic install, SSH in and run `php please starter-kit:install thoughtco/statamic-starter-kit`:
- **Clear Site:** Yes in most cases as it will generally be a blank slate you’re running from
- **Super Admin:** No, users are already setup for TC Staff.

One installed enter values in the prompt to update the APP_NAME, APP_KEY and APP_URL in the .env file.

By default the following packages are added:

**Cookie Panel:**
- Outputs a cookie panel
- Requires setup in the global config where specification of various cookies used on sites need to be added.
- Not needed on every site, so isn’t brought into the layout via Antlers.
- Full output information is available at https://packagist.org/packages/thoughtco/statamic-cookiepanel

**Livewire**
- A Laravel Livewire integration for Statamics Antlers engine.
- Full information for this integration is available at https://packagist.org/packages/marcorieser/statamic-livewire
- Full documentation on Livewire is available at https://livewire.laravel.com

**Redirects:**
- Some users like to make redirects / nicer urls for advertising etc. This add-on allows these to be generated by the user whilst specifying if they’re a 301 (Permanent) or 302 (Temporary) 

**Postmark Spam Check**
- Checks form submission content against Postmarks spam check API
- Full documentation at https://packagist.org/packages/thoughtco/statamic-postmark-spamcheck

**Scheduled Cache Invalidator**
- A command to help invalidate the cache when scheduled Statamic entries are due to go live.
- Full documentation at https://packagist.org/packages/mitydigital/statamic-scheduled-cache-invalidator

**SEOPro:**
- Allows the Search Engine Management of each entry and can be configured using the statamic/seo-pro.php config file
- Allows the production of sitemap.xml
- Read the documentation at https://statamic.com/addons/statamic/seo-pro/docs
- Make sure any collections that don't need SEO have that disabled on a sitewide basis.
- Look at adding a default image.
- If we're not using the assets container and are using a different container, this will need changed in the config file. 

**Social Links:**
- Some sites have articles (news / events .etc) which have sharing icons on them. This add-on generates the links for you so all you need to do is drop in the antlers tag. Documentation is a https://statamic.com/addons/aerni/social-links. 

**Statamic CP Resources:**
- Allows the inclusion of any videos generated on for clients. If you generate a video on Loom it should be added here and the client pointed to this page.

**Static Cache Manager**
- Clear specific paths in your static cache.
- Full documentation at https://statamic.com/addons/duncanmcclean/static-cache-manager


### ENV Variables
- GOOGLE_MAPS_GEOCODING_API_KEY: the api key to be used across the site. Information on how to obtain a key is found at https://www.loom.com/share/1e8a12938d6c459b8750c4a876680293?sid=60bfdbac-044d-4d46-97a7-442fadfa02e8.

### Postmark
We don’t use our own servers to send emails from, we rely on Postmark for delivery with the added bonus of we get visibility on deliverability .etc.

- Create Postmark server at www.postmarkapp.com - if you need an account setup, let me know. 
    - This is a two step process
        - Create the server within Postmark
        - Create the sender signature.
        - Video: https://loom.com/share/f11fb8314ba74f66be033b049e433fa3
        - Add the API Key to POSTMARK_TOKEN
        
### Blueprints

#### Panels in fieldsets
- We generally setup sites to run off a replicator field type. 
- Individual panels should be added as fieldsets and then add to the replicator field type
- Please try and get into the habit of this.
    - It allows you the ability to edit the panel once.
    - It allows the panel to be re-used in different areas of the website if necessary.

### Fieldsets
There are three fieldsets setup in the default build:
- **Button:** a link fieldset that will output a elements based on the information passed from each button. If more links are needed in a row, then the button fieldtype should be added to a replicator.

```
{{ if link_type }}
  {{ partial:_partials/snippets/links class="your classes" }}
{{/if }}
```

will output something similar to:

```<a href="https://www.thoughtcollective.com" rel="noopener" target="_blank" title="Title Attribute Text=" aria-label="Aria Label Text" class="your classes"></a>```

If you don't need any classes, simply leave the attribute out. Any classes should be specified directly in the html and not left to the client to input.

- **Video:** A series of fields that will allow the output of a 3rd party or local video using the code below. This should be used for all video output.

```
  {{ partial:_partials/snippets/video video_type="{video_type}" }}
```

- **Slider Settings:** Contains Slider Effect, Time Delay and pagination and should be used on sliders with the relevant data attribute for the slider. The data attributes are available in swiper-setup.js.

### Globals
There are 4 globals setup
- **Contact:**
  - Basic fields for main points of contact
- **Social Media:**
  - The social media handles associated with the website.
- **Cookie Panel:**
  - Contains settings relevant to the Cookie panel. This may not be relevant to the site you’re on, so if it’s not, please remove so as not to confuse the client.

### Collections
May need mounted to relevant page entry if a new one is added. The news collection needs to mounted to the relevant news page.

### Users
Admin role, need to add permissions to it if collections etc are added.

### Image Partial
The generation of the picture element has now been moved into a partial.
```
  {{ partial src="_partials/snippets/images" :image="image" cover="false" sizes="(min-width: 1280px) 960px, (min-width: 768px) 50vw, 20vw" lazy="true" }}
```

The following parameters can be passed:

**@param image:** 
An image URL. (required)

**@param sizes:** 
The sizes attribute. Something like `(min-width: 768px) 55vw, 90vw` for example.

**@param aspect_ratio:** 
Pass in an aspect ratio to crop the image in a certain way. `16/9` for example or specify a second ratio for larger screens: `1/1 large:1/2`.

**@param skip_ratio_steps:** 
Integer. Skip 1, 2 or 3 ratio steps to force small screens rendering big images to use mobile cropping instead of `large` cropping.

**@param srcset_from:** 
The path to a partial with an alternative srcset definition array. Something like `snippets/srcset_full_width` for example.

**@param class:** 
Add optional CSS classes.

**@param cover:** 
Boolean. Whether the image should cover the parent. Uses the focus position.

**@param bg:** 
String. Sets a background color for transparent images.

**@param blur:** 
Integer. Adds a blur effect to the image. Use values between 0 and 100.

**@param brightness:** 
String. Adjusts the image brightness. Use values between -100 and +100, where 0 represents no change.

**@param contrast:** 
String. Adjusts the image contrast. Use values between -100 and +100, where 0 represents no change.

**@param filter:** 
String. Applies a filter effect to the image. Accepts `greyscale` or `sepia`.

**@param flip:** 
String. Flips the image. Accepts `v`, `h` and `both`.

**@param gamma:** 
Float. Adjusts the image gamma. Use values between 0.1 and 9.99.

**@param orient:** 
String. Rotates the image. Accepts `auto`, `0`, `90`, `180` or `270`.

**@param sharpen:** Integer. Sharpen the image. Use values between 0 and 100.

**@param pixelate:** 
Integer. Applies a pixelation effect to the image. Use values between 0 and 1000.

**@param lazy:** 
Boolean. Whether the image should be natively lazy loaded.

**@param quality:** 
Integer. Set image quality. Defaults to 85.

**Additional Image Information**
We use the BlurHash addon to create a blurred version of the image and then when the image has loaded the main image is brought in.
See https://packagist.org/packages/thoughtco/statamic-blurhash for more information on image blurring. Front End should deal with the 
transformation from the blurred image to the live image.

**Outputting Forms**
Forms use the concept of a 'driver' (either 'precognition', 'livewire' or blank) so the appropriate code is loaded depending on the context the are used.

The fields loop should now become the below for precognition:

```
{{ fields }}
    {{ partial:if_exists src="_partials/forms/fields/{{ type }}" driver="precognition" }}
{{ /fields }}
```
The below for LiveWire (model_prefix is the path to the model we're updating, eg data.):

```
{{ fields }}
    {{ partial:if_exists src="_partials/forms/fields/{{ type }}" driver="livewire" model_prefix="data." }}
{{ /fields }}
```

If you want to show the labels:

```
{{ fields }}
    {{ partial:if_exists src="_partials/forms/fields/{{ type }}" show_label="true" }}
{{ /fields }}
```

### Clearing cache

Clearing the cache using `php artisan cache:clear` will no longer clear the stache or asset cache. Most of the time this is what you want as rebuilding the stache can be slow.

If you want to manually clear and re-warm the stache use `php please stache:clear && php please stache:warm`.

If you want to clear the assets meta cache use: `php artisan cache:clear asset_meta`. If you want to clear the assets folder cache use: `php artisan cache:clear asset_container_contents`. Most of the time you'll want to do both: `php artisan cache:clear asset_meta && php artisan cache:clear asset_container_contents`

