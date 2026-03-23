module.exports = function(grunt) {

    cfg = grunt.file.readJSON('inc/template.json');

    // var PathMaster = 'di2024';
    var PathMaster = cfg['template'];
    var PathDist = 'view';

    console.log('-------------------------------');
    console.log('Template '+PathMaster);
    console.log('-------------------------------');

    grunt.initConfig({

        clean: {
            assets: [
                'view/**/*.css',
                'view/**/*.js',
                'view/**/*.html',
                'view/**/*.shtml',
                'view/**/*.latte'
            ],
            options: {
                force: true
            }
        },

        copy: {
            target: {
                files: [
                    {
                        expand:true,
                        cwd: PathMaster,
                        src: [
                            '**/*.html',
                            '**/*.shtml',
                            '**/*.latte',
                            '**/*.json'
                        ],
                        dest: 'view/'
                    },
                    {
                        expand:true,
                        cwd: PathMaster+'/img/',
                        src: ['**/*.*'],
                        dest: 'view/img/'
                    },
                    // {
                    //     expand:true,
                    //     cwd: PathMaster+'/plugins/fonts/',
                    //     src: ['*.*'],
                    //     dest: 'view/fonts/'
                    // },
                ]
            }
        },

        cssmin: {
            options: {
                keepSpecialComments: false
            },
            target: {
                files: {
                    "view/css/main.min.css": [
                        PathMaster + '/plugins/core/bootstrap.css',
                        PathMaster + '/plugins/css/*.css',
                        PathMaster + '/css/*.css'
                    ],
                    "view/css/imprimir.min.css": [
                        PathMaster + '/css/imprimir.css'
                    ]
                }
            }
        },

        uglify: {
            options: {
                 preserveComments: false
            },
            target: {
                // files: {
                //     'view/js/core.min.js' : [
                //         PathMaster+'/plugins/core/jquery.js',
                //         PathMaster+'/plugins/core/bootstrap.js'
                //     ],
                //     'view/js/plugins.min.js' : [PathMaster+'/plugins/js/*.js'],
                //     'view/js/main.min.js' : [PathMaster+'/js/*.js']
                // }
                files: {
                    'view/js/main.min.js' : [
                        PathMaster + '/plugins/core/jquery.js',
                        PathMaster + '/plugins/core/bootstrap.js',
                        PathMaster + '/plugins/js/*.js',
                        PathMaster + '/js/*.js'
                    ]
                }
            }
        },

        // usemin: {
        //     html: 'view/**/*.html'
        // },

        htmlmin: {
            options: {
              removeComments: false,
              collapseWhitespace: true,
            //   conservativeCollapse: true,
              preserveLineBreaks: false,
              caseSensitive: true
            },
            views: {
                expand: true,
                src: ['view/**/*.html', 'view/**/*.latte']
            }
        },

        cacheBust: {
            options: {
                assets: ['view/css/*','view/js/*'],
                deleteOriginals: true,
                jsonOutput: true,
            },
            src: ['view/**/*.html', 'view/**/*.shtml', 'view/**/*.latte']
        },

        replace: {
          assets: {
            src: ['view/**/*.html','view/**/*.shtml','view/**/*.latte'],
            overwrite: true,
            // vai receber replaceAssets
            replacements: []
          },
          removeJsComments: {
            src: ['view/**/*.html', 'view/**/*.latte'],
            overwrite: true,
            replacements: [{
                from: /<!-- JS -->[\s\S]*?<!-- \/\/JS -->/g,
                to: ''
            },{
                from: /<!-- CSS -->[\s\S]*?<!-- \/\/CSS -->/g,
                to: ''
            }]
          }
        },

    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-htmlmin');
    // grunt.loadNpmTasks('grunt-usemin');
    grunt.loadNpmTasks('grunt-cache-bust');
    grunt.loadNpmTasks('grunt-text-replace');

    grunt.registerTask('default', [
        'clean',
        'copy',
        'cssmin',
        'uglify',
        // 'usemin',
        'htmlmin',
        'cacheBust',
        'replaceAssets',
        'replace'
    ]);

    grunt.registerTask('replaceAssets', 'Trocando as referencias', function(){
        var json = grunt.file.readJSON('grunt-cache-bust.json');
        var rep = [];
        for (var key in json) {
            de = key.replace('view/','');
            para = json[key].replace('view/','');
            grunt.log.writeln(">> Troca "+de+" por "+para);
            rep.push({
                from: de,
                to: para,
            });
        }
        grunt.config('replace.assets.replacements', rep);
    });

};
