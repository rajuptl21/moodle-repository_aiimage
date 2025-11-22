# AI Image Repository (AI Image Generation) plugin for Moodle

The AI Image repository plugin for Moodle adds an AI image generation and editing tool that is accessible from the HTML editor and file managers via the repository system.

It will work with the Moodle AI subsystem provider (OpenAI, Gemini etc) or with a [Poodll](https://poodll.com) subscription (Poodll Media, Poodll Languages, Poodll Essentials, or a Poodll free trial). 

## Features
- Image Generation: Generate images from text prompts.
- Image Editing: Edit existing images from text prompts and an existing image.(Poodll or Gemini only)

## Installation
1. Clone the repository or download the plugin
2. Place the `aiimage` folder in the `repository` directory of your Moodle installation.
3. Navigate to the Site administration > Notifications page in Moodle to complete the installation.
4. Configure the plugin settings by going to Site administration > Plugins > Repositories > Manage Repositories. There set "AI Image" to "Enabled and Visible." This will take you to the settings page.
5. On the settings page you will need choose your AI provider.

## Usage

- In the *HTML editor* click the "Add media" button. Choose the "Browse repositories" option and choose "AI Image" from the list of repositories. 
- OR in the *File Manager* click the add file icon (usually '+') to browse repositories and choose "AI Image" from the list. 

### Generating new images
1. Enter a prompt
2. Select the type of image from the image options.
3. If there are other images already in the list, be sure to select "Do not use an existing image."
4. Click "Make Image."
5. It will take up to a minute  and then the image will be presented.
6. Click to confirm you want to use it and add it to your content.
 
### Editing existing images
1. Select an existing image from the list of images.
2. Enter a prompt for how you want to edit the image.  
3. Click "Make Image." (NB *The type of image is ignored when editing images* ). 
4. It will take up to a minute and then the edited image will be presented.
5. Click to confirm you want to use it and add it to your content.
6. Choose to "overwrite" the existing image or accept the renamed version to keep both images.

## Support
At the time of writing (November 2025) the plugin is still early stage. But contact us via https://support.poodll.com if you need help.
