# PluginVisualisation
Find all plugins in magento installation - heavily based on hackathon module PluginVisualisation

## Based on 
https://github.com/magento-hackathon/magento2-plugin-visualization

...which I did not get to work but loved the idea.
I reused some of the code and wrote my own version for magento 2.4.4 and up 

## Usage
- Install
- Afterwards you will find a new CLI command called sols:list:plugins
- you can use it like so : bin/magento sols:list:plugins --list_plugins_csv Plugins.csv
- after it is done, open directory var/exports and find your newly created Plugins.csv there.

This works on 2.4.4 and up 

## Questions? 
I hope that the plugin is so simple that no questions are needed but if you do have some, 
make an issue. 

## Future
I hope to add more functionality to it in future.
