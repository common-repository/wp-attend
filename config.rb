# Require any additional compass plugins here.
# Set this to the root of your project when deployed:
http_path = "/"
# Path for images
http_images_path = "/images/"
http_generated_images_path = "/images/"

css_dir = "styles"
sass_dir = "sass"
images_dir = "public/images"
javascripts_dir = "js"

output_style = :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true
# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false
# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass
css_dir = "styles" # by Compass.app 
sass_dir = "sass" # by Compass.app 
images_dir = "public/images" # by Compass.app 
output_style = :expanded # by Compass.app 
relative_assets = false # by Compass.app 
line_comments = true # by Compass.app 
sass_options = {:debug_info=>false} # by Compass.app 
sourcemap = false # by Compass.app 