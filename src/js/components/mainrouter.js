import React, { PropTypes } from 'react'
import { Router, Route, IndexRoute, hashHistory } from "react-router";

import Layout from './layout'
import IndexC from './index'

import CharacterInfo from '../pages/characterinfo'

class MainRouter extends React.Component {
  fetchCharacterInfo(nextState) {
    /*const name = nextState.params.name;
    const name2 = nextState.params.name2;
    console.log("Fetch! ", name);
    DataActions.changePlayer(name, name2);*/
  }

  render () {
    return(
      <Router history={hashHistory}>
        <Route path="/" component={Layout}>
          <IndexRoute component={IndexC}></IndexRoute>
          <Route path="player/:name(/:name2)" component={CharacterInfo} onEnter={this.fetchCharacterInfo}></Route>
        </Route>
      </Router>
    );
  }
}

export default MainRouter;
