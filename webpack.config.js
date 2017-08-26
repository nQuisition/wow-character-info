var path = require('path');

module.exports = {
  entry: './src/js/app.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'bundle.js',
    publicPath: '/dist'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['react', 'es2015', 'stage-2'],
              plugins: ['react-html-attrs', 'transform-class-properties'/*, 'transform-decorations-legacy'*/]
            }
          }
        ]
      },
      {
        test: /.*\.(gif|png|jpe?g)$/i,
        loader: 'file-loader?name=/media/images/[name].[ext]'
      }
    ]
  }
};
