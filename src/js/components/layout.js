import React from "react"
import Header from "./header"

export default class Layout extends React.Component
{
  render()
  {
    return (
      <div class="main-wrapper">

        <Header />

        <div class="wl-main-container">

          {this.props.children}

        </div>
      </div>
    );
  }
}
