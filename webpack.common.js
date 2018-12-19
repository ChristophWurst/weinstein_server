const path = require('path');
const webpack = require('webpack');

module.exports = {
    entry: {
        weinstein: path.join(__dirname, 'src/weinstein.js')
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'public/js')
    },
    node: {
        fs: 'empty'
    },
    module: {
        rules: [
            {
                test: /davclient/,
                use: 'exports-loader?dav'
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.hb$/,
                loader: "handlebars-loader"
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.less$/,
                use: ['style-loader', 'css-loader', 'less-loader']
            },
            {
                test: /\.(png|jpg|gif)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]?[hash]'
                }
            },
            {
                test: /\.(svg|woff|woff2|ttf|eot)$/i,
                use: [
                    {
                        loader: 'url-loader'
                    }
                ]
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            '_': "underscore",
            $: "jquery",
            jQuery: "jquery"
        })
    ],
    resolve: {
        extensions: ['*', '.js', '.json'],
        symlinks: false
    }
};