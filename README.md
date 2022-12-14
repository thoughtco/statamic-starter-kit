# Starter Kit
The starter kit is an attempt give you the additional extras TC run on top of a Statamic install.  If you feel something else which is used on a regular basis should be installed, let us know and we can have a look.

### Install

On the blank Statamic install, SSH in and run `php please starter-kit:install thoughtco/statamic-starter-kit`:
- **Clear Site:** Yes in most cases as it will generally be a blank slate you’re running from
- **Super Admin:** No, users are already setup for TC Staff.

One installed update the .env:
- Update ``APP_NAME`` in env
- Update ``APP_URL`` in env or else you’ll get 404

By default the following packages are added:

**Sitemapamic:**
- Read the documentation. Any collections / taxonomies not generating viewable pages should be added to the config so they are ignored 
- This allows the generation of sitemaps and can be configured using the Sitemapamic config file

**SEOPro:**
- Allows the Search Engine Management of each entry and can be configured using the statamic/seo-pro.php config file
- Allows the production of sitemap.xml
- Read the documentation at https://statamic.com/addons/statamic/seo-pro/docs
- Make sure any collections that don't need SEO have that disabled on a sitewide basis.
- Look at adding a default image.
- If we're not using the assets container and are using a different container, this will need changed in the config file. 
  
**Duplicator:**
- Allows the user to duplicate entries in the control panel
- Required on all sites 

**Social Links:**
- Some sites have articles (news / events .etc) which have sharing icons on them. This add-on generates the links for you so all you need to do is drop in the antlers tag. Documentation is a https://statamic.com/addons/aerni/social-links. 

**Iconamic:**
- Some sites have SVG icons that the user needs to be able to choose from. This add-on is an SVG icon picker field type and tag. 

**Redirects:**
- Some users like to make redirects / nicer urls for advertising etc. This add-on allows these to be generated by the user whilst specifying if they’re a 301 (Permanent) or 302 (Temporary) 

**Minify:**
- Minifies CSS and JS 

**Cookie Panel:**
- Outputs a cookie panel
- Requires setup in the global config where specification of various cookies used on sites need to be added.
- Not needed on every site, so isn’t brought into the layout via Antlers.
- Full output information is available at https://packagist.org/packages/thoughtco/statamic-cookiepanel

### Postmark
We don’t use our own servers to send emails from, we rely on Postmark for delivery with the added bonus of we get visibility on deliverability .etc.

- Create Postmark server at www.postmarkapp.com - if you need an account setup, let me know. 
    - This is a two step process
        - Create the server within Postmark
        - Create the sender signature.
        - Video: https://loom.com/share/f11fb8314ba74f66be033b049e433fa3
        - Add the API Key to POSTMARK_TOKEN

### Body Tag
- Add  data-csrf="{{ csrf_token }}" on layout.antlers.html, if it’s not already there.

### Blueprints

#### Panels in fieldsets
- We generally setup sites to run off a replicator field type. 
- Individual panels should be added as fieldsets and then add to the replicator field type
- Please try and get into the habit of this.
    - It allows you the ability to edit the panel once.
    - It allows the panel to be re-used in different areas of the website if necessary.

### Fieldsets
There are two fieldsets setup in the default build:
- **Link:** contains an url field, a text field and a toggle for a new window
- **Slider Settings:** Contains Slider Effect and Time Delay and should be used on sliders with the relevant data attribute for the slider. The data attributes are available in swiper-setup.js

### Globals
There are 4 globals setup
- **Contact:**
  - Basic fields for main points of contact
  - Google API Key: Every site should have a separate API key. If you’re unsure of where to do this, let Ryan or I know.
- **Mailing List:**
  - Choose provider (currently Campaign Monitor or MailChimp)
  - Fill in the API Details as requested.
  - This then hooks into the Newsletter form and sends the information to the relevant provider.
  - Please test this is working to the relevant list.
- **Social Media:**
  - The social media handles associated with the website.
- **Cookie Panel:**
  - Contains settings relevant to the Cookie panel. This may not be relevant to the site you’re on, so if it’s not, please remove so as not to confuse the client.

### Newsletters
There’s now a new global in place allowing you to choose where you want the newsletter signup to go and also requires to you enter api keys / list ids .etc.

### Collections
May need mounted to relevant page entry if a new one is added. The news collection needs to mounted to the relevant news page.

### Users
Admin role, need to add permissions to it if collections etc are added.

### Glide
Depending on the site it may be useful to use Glide Presets. The plus point on this is that is generates the various image sizes on upload meaning that the page load time drops further. The downside is, when you upload an image it creates a version of the image for each preset so disk space can get further clogged. So use presets on a per site basis.

#### Presets
More information on presets can be found at https://statamic.dev/image-manipulation#presets

### General Glide Use
- DPR
  - Every glide tag should carry a dpr=“2” tag and the width and height should be the dimensions that are output on the screen.
  - DPR will double these dimensions for retina displays.
- Format
  - Should be set to webp
  - Webp is a modern image format that provides superior lossless and lossy compression for images on the web.

More information on Glide is available at https://statamic.dev/tags/glide

### Forms
The form will initially need added in Statamic Forms. Once added it can be output using the following:
``{{ form:form_handle in=“form_handle” data-ajaxform=“true” data-customerror="true" }}``

**``data-ajaxform=“1”`` submits the form over `XHR`.**
- To run this you need to use the Git repo, Codex/Statamic/Forms … namely forms.js and FormErrors.js. 
- There should be a <div class=“success”> with the success message in it, and form fields should be wrapped in a <div class=“fields”>. Errors will be output either inside a <div class=“errors”> or a <span class=“error”> after each form field, whichever you prefer.

**Remove ``data-ajaxform`` will submit the form by a ``POST`` redirect.**
- Again, success / error messages will need to be output.
  
**Outputting Forms**
With the probable exception of a newsletter form, you add the form fields to the html using the forms partial within Thought Co Github Codex Repo. 
Some HTML may need to be modified but it should give you everything you need out of the box.

**Captcha**
All forms should use the Captcha add on (installed). Full details are at https://statamic.com/addons/aryeh-raber/captcha.
We'll be using Turnstile in Cloudflare so let Ryan or Andi know when you need the keys.

**Additional Image Information**
We use the BlurHash addon to create a blurred version of the image and then when the image has loaded the main image is brought in.
See https://packagist.org/packages/thoughtco/statamic-blurhash for more information on image blurring. Front End should deal with the 
transformation from the blurred image to the live image.