import React, { PropTypes } from 'react'
import { connect } from 'react-redux';

import * as playerActions from '../actions/playeractions';

import moment from 'moment'
import 'moment-timezone'
import Chart from 'chart.js'
import {Line as LineChart} from 'react-chartjs-2'

import PlayerAdder from '../components/playeradder'

import icon_hc from '../../../media/images/wow/skull.png'

class CharacterInfo extends React.Component {
  componentWillMount() {
    this.props.dispatch(playerActions.fetchPlayer('Quelthariel'));
  }

  render () {
    const { name } = this.props.params;
    const mainEncountersIndexed = this.props.playerMain.map((e, i) => {return {index:i, data:e}});
    const mainEncountersSorted = mainEncountersIndexed.sort((a,b) => (a.data.difficulty-b.data.difficulty)*1000 + a.index-b.index);
    const mainEncounters = mainEncountersSorted.map((e) => e.data);

    const difficultyNames = ['LFR', 'Flex', 'Normal', 'Heroic', 'Mythic'];

    console.log(this.props.playerMain, this.props.playerOthers);
    const items = mainEncounters.map((e, i) =>
    {
      const otherEncounters = this.props.playerOthers.find((element) => (element.name === e.name && element.difficulty === e.difficulty)?true:false);
      return <CharacterInfoItem bossName={e.name} diff={e.difficulty}
              diffName={difficultyNames[e.difficulty-1]} specs={e.specs} others={otherEncounters.players} key={i}/>
    });

    return(
      <div>
        <PlayerAdder/>
        <div class="article-title">
          <h1>{name}</h1>
        </div>

        <ul>
          {items}
        </ul>
      </div>
    );
  }
}

class CharacterInfoItem extends React.Component {
  render () {
    //TODO filter this out when getting data?
    const specs = this.props.specs.filter((e) => (e.spec === 'Ranged' || e.spec === 'Melee')?false:true);
    const items = specs.map((e, i) => <SpecItem className={e.class} specName={e.spec} data={e.data} others={this.props.others} key={i}/>);
    let difficulty = this.props.diffName;
    if(this.props.diff === 3)
      difficulty = '';
    else if(this.props.diff === 4)
      difficulty = <img src={icon_hc} alt={this.props.diffName}/>;

    return(
      <li>
        {this.props.bossName} {difficulty}
        <br/>
        {items}
        <br/><br/>
      </li>
    );
  }
}

class SpecItem extends React.Component {
  render () {
    const items = this.props.data.map((e, i) => <ParseItem dps={e.persecondamount} startTime={e.start_time} ilvl={e.ilvl} key={i}/>);
    const mainDataPoints = this.props.data.sort((a,b) => a.start_time < b.start_time ? -1 : (a.start_time > b.start_time ? 1 : 0)).map((e, i) => {return {x:e.start_time, y:e.persecondamount}});
    const raidStart = moment(1498046400000).tz('Europe/Paris');

    let datasets = [{
      label: 'DPS',
      data: mainDataPoints,
      backgroundColor: 'rgba(123, 83, 252, 0.8)',
      borderColor: '#0000FF',
      borderWidth: 3,
      lineTension: 0,
      fill: false
    }];

    this.props.others.forEach((e) => {
      const dataPoints = e.encounters.sort((a,b) => a.start_time < b.start_time ? -1 : (a.start_time > b.start_time ? 1 : 0)).map((e, i) => {return {x:e.start_time, y:e.persecondamount}});
      datasets.push({
        label: e.name,
        data: dataPoints,
        backgroundColor: 'rgba(123, 83, 252, 0.8)',
        borderColor: '#FF0000',
        borderWidth: 3,
        lineTension: 0,
        fill: false
      });
    });

    const chartOptions = {
      animation: false,
      maintainAspectRatio: true,
      tooltips: {
        mode: 'x',
        intersect: 'false'
      },
      legend: {
        display: true,
        position: 'top',
        labels: {
          boxWidth: 80,
          fontColor: 'white'
        }
      },
      scales: {
        xAxes: [{
          type: "time",
          time: {
            //min: raidStart,
            unit: 'day',
            unitStepSize: 1,
            tooltipFormat: "MMM DD, HH:mm",
            displayFormats: {
              day: 'MMM DD'
            }
          }
        }],
        yAxes: [{
          ticks: {
                beginAtZero: true
          },
          gridLines: {
            color: "#AAAAAA",
            borderDash: [2, 2],
          },
          scaleLabel: {
            display: true,
            labelString: "DPS",
            fontColor: "green"
          }
        }]
      }
    };

    const chartData = {
      datasets: datasets
    };

    return(
      <div>
        Class : {this.props.className}; Spec : {this.props.specName}
        <LineChart data={chartData} width={600} height={250} options={chartOptions}/>
        {items}
      </div>
    );
  }
}

class ParseItem extends React.Component {
  render () {
    const formattedStart = moment(this.props.startTime).tz('Europe/Paris').format('MMM DD HH:mm');
    return(
      <div>
        {formattedStart} DPS : {this.props.dps}; ilvl : {this.props.ilvl}
      </div>
    );
  }
}

export default connect((store) => {
  return {
    playerMain: store.player.main,
    playerOthers: store.player.others,
    playerFetching: store.player.fetching,
    playerFetched: store.player.fetched,
    error: store.player.error
  };
})(CharacterInfo);
