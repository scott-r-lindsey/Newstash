import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

export default class Tos extends React.Component {
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
          <title>Terms of Service</title>
        </Helmet>
        <strong>This is a stub</strong>
      </div>
    );

  }
}
