import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

// Simple example of a React "smart" component
export default class Home extends React.Component {
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
          <title>Books to Love</title>
        </Helmet>
        <strong>Greetings fellow react devs</strong>
        <Link to="/book/3126/how-smart-machines-think-the-mit-press-by-sean-gerrish">A Book</Link>
      </div>
    );

  }
}
