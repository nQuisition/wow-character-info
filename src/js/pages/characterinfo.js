import React, { PropTypes } from 'react'
import { connect } from 'react-redux';

import * as characterActions from '../actions/characteractions';

import moment from 'moment'
import 'moment-timezone'

import icon_hc from '../../../media/images/wow/skull.png'

class CharacterInfo extends React.Component {
  componentWillMount() {
    this.fetchCharacter(this.props.params.name);
  }

  componentWillReceiveProps(nextProps) {
    if(this.props.params.name !== nextProps.params.name) {
      this.fetchCharacter(nextProps.params.name);
    }
  }

  fetchCharacter(name) {
    this.props.dispatch(characterActions.fetchCharacter(name));
  }

  render () {
    if(this.props.character === null) {
      return(<div></div>);
    }
    const { name } = this.props.character;

    return(
      <div>
        <div class="article-title">
          <h1>{name}</h1>
        </div>
      </div>
    );
  }
}

export default connect((store) => {
  return {
    character: store.character.character,
    characterFetching: store.character.fetching,
    characterFetched: store.character.fetched,
    error: store.character.error
  };
})(CharacterInfo);
