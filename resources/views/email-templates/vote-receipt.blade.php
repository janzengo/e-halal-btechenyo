<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Vote Receipt - E-Halal BTECHenyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        @media only screen and (max-width: 600px) {
            .container {
                padding: 15px 10px !important;
            }
            .main-content {
                margin-top: 30px !important;
                padding: 30px 15px 40px !important;
            }
            .vote-ref {
                font-size: 28px !important;
                letter-spacing: 8px !important;
                word-spacing: 5px !important;
                word-break: keep-all !important;
                white-space: nowrap !important;
                overflow-x: auto !important;
            }
            .header-table {
                display: block !important;
            }
            .header-logo {
                display: block !important;
                text-align: center !important;
                margin-bottom: 10px !important;
            }
            .header-date {
                display: block !important;
                text-align: center !important;
            }
            .contact-info {
                max-width: 100% !important;
                padding: 0 10px !important;
                font-size: 13px !important;
            }
            .footer {
                max-width: 100% !important;
                padding: 0 10px !important;
                font-size: 12px !important;
            }
            h1 {
                font-size: 20px !important;
                line-height: 1.3 !important;
                word-break: keep-all !important;
            }
            p {
                font-size: 14px !important;
                line-height: 1.5 !important;
            }
            .votes-table {
                font-size: 12px !important;
            }
            .votes-table th,
            .votes-table td {
                padding: 8px 6px !important;
            }
        }
        
        @media only screen and (max-width: 480px) {
            .container {
                padding: 10px 8px !important;
            }
            .main-content {
                padding: 25px 12px 35px !important;
            }
            .vote-ref {
                font-size: 24px !important;
                letter-spacing: 6px !important;
            }
            h1 {
                font-size: 18px !important;
            }
            p {
                font-size: 13px !important;
            }
            .votes-table {
                font-size: 11px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; font-family: 'Poppins', sans-serif; background: #ffffff; font-size: 14px;">
    <div class="container" style="max-width: 680px; margin: 0 auto; padding: 45px 30px 60px; background: #f4f7ff; background-image: url('https://res.cloudinary.com/dvlztedcm/image/upload/v1759494619/hero-image_t6seuv.jpg'); background-repeat: no-repeat; background-size: 800px 452px; background-position: top center; font-size: 14px; color: #434343;">
        
        <header>
            <table class="header-table" style="width: 100%;">
                <tbody>
                    <tr style="height: 0;">
                        <td class="header-logo">
                            <img src="https://res.cloudinary.com/dvlztedcm/image/upload/v1759494530/white-logo_spfyuh.png" alt="E-Halal BTECHenyo" height="60px" style="display: block;" />
                        </td>
                        <td class="header-date" style="text-align: right;">
                            <span style="font-size: 16px; line-height: 30px; color: #ffffff;">
                                {{ $votedAt }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </header>

        <main>
            <div class="main-content" style="margin: 0; margin-top: 70px; padding: 92px 30px 115px; background: #ffffff; border-radius: 30px; text-align: center;">
                <div style="width: 100%; max-width: 489px; margin: 0 auto;">
                    <h1 style="margin: 0; font-size: 24px; font-weight: 500; color: #1f1f1f;">
                        Vote Receipt
                    </h1>
                    <p style="margin: 0; margin-top: 17px; font-size: 16px; font-weight: 500;">
                        Hello {{ $voterName }},
                    </p>
                    <p style="margin: 0; margin-top: 17px; font-weight: 500; letter-spacing: 0.56px; line-height: 1.6;">
                        Thank you for participating in the <strong>{{ $electionName }}</strong>. Your vote has been successfully recorded and processed.
                    </p>
                    
                    <div style="margin-top: 40px; padding: 25px; background: #f8fafc; border-radius: 15px; border: 2px solid #e2e8f0;">
                        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 600; color: #259646;">Reference</h3>
                        <p class="vote-ref" style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: 4px; color: #259646; word-break: break-all;">
                            {{ $voteRef }}
                        </p>
                    </div>

                    <div style="margin-top: 30px; text-align: left;">
                        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 600; color: #259646; text-align: center;">Votes Cast</h3>
                        
                        <!-- Votes Table -->
                        <table class="votes-table" style="width: 100%; border-collapse: collapse; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <thead>
                                <tr style="background: #259646;">
                                    <th style="padding: 12px 15px; text-align: left; color: #ffffff; font-weight: 600; font-size: 14px; border: none;">Position</th>
                                    <th style="padding: 12px 15px; text-align: left; color: #ffffff; font-weight: 600; font-size: 14px; border: none;">Candidate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($votes as $vote)
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 12px 15px; color: #374151; font-size: 14px; border: none; font-weight: 500;">{{ $vote['position'] }}</td>
                                    <td style="padding: 12px 15px; color: #374151; font-size: 14px; border: none;">
                                        <strong>{{ $vote['candidate'] }}</strong>
                                        @if(isset($vote['party']) && $vote['party'])
                                            <br><span style="color: #6b7280; font-size: 12px;">{{ $vote['party'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 30px; padding: 20px; background: #ecfdf5; border-radius: 12px; border-left: 4px solid #259646;">
                        <p style="margin: 0; font-size: 14px; color: #065f46; font-weight: 500;">
                            ✅ Your vote has been successfully recorded and is secure. Keep this receipt for your records.
                        </p>
                    </div>
                </div>
            </div>

            <p class="contact-info" style="max-width: 400px; margin: 0 auto; margin-top: 90px; text-align: center; font-weight: 500; color: #8c8c8c; line-height: 1.6;">
                Need help? Contact us at
                <a href="mailto:admin@ehalal.tech" style="color: #259646; text-decoration: none; word-break: break-all;">
                    admin@ehalal.tech
                </a>
                or visit our office at 2nd Floor, BMG Building, Barrera Street, Poblacion, Baliuag City, Bulacan
            </p>
        </main>

        <footer class="footer" style="width: 100%; max-width: 490px; margin: 20px auto 0; text-align: center; border-top: 1px solid #e6ebf1;">
            <p style="margin: 0; margin-top: 40px; font-size: 16px; font-weight: 600; color: #434343;">
                E-Halal BTECHenyo
            </p>
            <p style="margin: 0; margin-top: 8px; color: #434343;">
                Dalubhasaang Politeknikong ng Lungsod ng Baliuag
            </p>
            <p style="margin: 0; margin-top: 16px; color: #434343; font-size: 12px;">
                © {{ date('Y') }} E-Halal BTECHenyo. All rights reserved.
            </p>
        </footer>
    </div>
</body>
</html>