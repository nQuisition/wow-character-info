import React, { PropTypes } from 'react'
import { connect } from 'react-redux';

import * as playerActions from '../actions/playeractions';

class PlayerAdder extends React.Component
{
  constructor() {
    super();
    this.state = {
      inputValue: ''
    };
    this.addPlayer = this.addPlayer.bind(this);
  }
  addPlayer() {
    this.props.dispatch(playerActions.fetchPlayer(this.state.inputValue));
  }
  updateInputValue(evt) {
    this.setState({
      inputValue: evt.target.value
    });
  }
  render() {
    return (
      <div>
        <input value={this.state.inputValue} onChange={evt => this.updateInputValue(evt)}/>
        <button onClick={this.addPlayer}>Add Player</button>
      </div>
    )
  }
}

export default connect((store) => {
  return {};
})(PlayerAdder);
