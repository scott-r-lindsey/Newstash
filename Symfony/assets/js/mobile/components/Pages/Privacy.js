import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

export default class Privacy extends React.Component {
  constructor(props, context) {
    super(props, context);
  }

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    return (
      <div>
        <Helmet>
          <title>Privacy Policy</title>
        </Helmet>
        <strong>Privacy Policy</strong>
      </div>
    );

  }
}
